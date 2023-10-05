<?php

// tests/Feature/Api/CheckoutPostTest.php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\CheckoutController;
use App\Http\Requests\Api\CheckoutRequest;
use App\Models\Bank;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Models\UserAddress;
use Database\Seeders\BankSeeder;
use Database\Seeders\CitySeeder;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Illuminate\Validation\Rule;
use Tests\TestCase;

class CheckoutPostTest extends TestCase
{
  use RefreshDatabase;

  private string $uri = '/api/checkout';
  private User $user;
  private Collection $banks;

  protected function setUp(): void
  {
    parent::setUp();
    $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class, BankSeeder::class]);
    $this->user = $this->createUser();
    $this->banks = Bank::all();
  }

  public function createCart(int $quantity = 1, ?array $productData = []): Cart
  {
    return Cart::factory()
      ->for(Product::factory()->create($productData))
      ->for($this->user)
      ->create(compact('quantity'));
  }

  private function attemptToCheckout(?array $cartIds = []): TestResponse
  {
    return $this->postJson(
      $this->uri,
      ['cart_ids' => $cartIds],
      $this->authBearerToken($this->user)
    );
  }

  private function attemptToCheckoutAndExpect422(array|string $errors, ?array $cartIds = []): TestResponse
  {
    return $this->attemptToCheckout($cartIds)
      ->assertUnprocessable()
      ->assertJsonStructure(['message', 'errors' => ['*' => []]])
      ->assertJsonValidationErrors($errors);
  }

  /** @test */
  public function checkout_uses_the_correct_form_request()
  {
    $this->assertActionUsesFormRequest(
      CheckoutController::class,
      'checkout',
      CheckoutRequest::class
    );
  }

  /** @test */
  public function checkout_request_has_the_correct_validation_rules()
  {
    $rules = (new CheckoutRequest())
      ->setUserResolver(fn () => $this->user)->rules();

    $this->assertValidationRules(
      [
        'cart_ids' => [
          'required',
          'array',
        ],
        'cart_ids.*' => [
          'integer',
          Rule::exists('carts', 'id')
            ->where('user_id', $this->user->id),
        ]
      ],
      $rules
    );
  }

  /** @test */
  public function cannot_checkout_if_user_is_not_authenticated()
  {
    $response = $this->postJson($this->uri, headers: ['Authorization' => 'Bearer Invalid-Token']);

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function can_checkout()
  {
    // Arrange
    $this->seed([ProvinceSeeder::class, CitySeeder::class,]);
    $userAddress = UserAddress::factory()->for($this->user)->create(['city_id' => 154]);
    $cart1 = $this->createCart(2, ['price' => 25000, 'stock' => 5, 'weight' => 500]);
    $cart2 = $this->createCart(1, ['price' => 50000, 'stock' => 5, 'weight' => 500]);
    $totalWeight = 1500;
    $totalQuantity = 3;
    $totalPrice = 100000;
    // Action
    $response = $this->attemptToCheckout([$cart1->id, $cart2->id]);
    // Assert
    $response->assertOk()
      ->assertJson([
        'data' => [
          'shipping_address' => $this->formatUserAddressData($userAddress),
          'carts' => [$this->formatCartData($cart1), $this->formatCartData($cart2)],
          'payment_methods' => [
            'bank' => $this->formatBankData($this->banks)
          ],
          'total_quantity' => $totalQuantity,
          'total_weight' => $totalWeight,
          'total_price' => $totalPrice,
        ]
      ])
      ->assertJsonFragment([
        'courier' => 'jne',
        'courier_name' => 'Jalur Nugraha Ekakurir (JNE)',
        'service' => 'REG',
        'service_name' => 'Layanan Reguler',
        'cost' => 34000,
        'etd' => '1-2 hari'
      ])
      ->assertJsonCount(8, 'data.shipping_methods')
      ->assertJsonCount(1, 'data.payment_methods.bank');
  }

  /** @test */
  public function can_checkout_without_a_shipping_address()
  {
    // Arrange
    $cart1 = $this->createCart(2, ['price' => 25000, 'stock' => 5, 'weight' => 500]);
    $cart2 = $this->createCart(1, ['price' => 50000, 'stock' => 5, 'weight' => 500]);
    $totalWeight = 1500;
    $totalQuantity = 3;
    $totalPrice = 100000;
    // Action
    $response = $this->attemptToCheckout([$cart1->id, $cart2->id]);
    // Assert
    $response->assertOk()
      ->assertExactJson([
        'data' => [
          'shipping_address' => null,
          'carts' => [$this->formatCartData($cart1), $this->formatCartData($cart2)],
          'shipping_methods' => null,
          'payment_methods' => [
            'bank' => $this->formatBankData($this->banks)
          ],
          'total_quantity' => $totalQuantity,
          'total_weight' => $totalWeight,
          'total_price' => $totalPrice,
        ]
      ])
      ->assertJsonCount(1, 'data.payment_methods.bank');
  }

  /** @test */
  public function cannot_checkout_if_product_is_not_published()
  {
    $cart1 = $this->createCart(1, ['stock' => 1, 'weight' => 100, 'is_publish' => false]);
    $cart2 = $this->createCart(3, ['stock' => 3, 'weight' => 100]);

    $this->attemptToCheckoutAndExpect422('product', [$cart1->id, $cart2->id])
      ->assertJsonPath('errors.product.0', 'The product must be published.');
    $this->assertDatabaseMissing('carts', $cart1->toArray());
  }

  /** @test */
  public function cannot_checkout_if_quantity_exceed_stock()
  {
    $cart1 = $this->createCart(3, ['stock' => 1, 'weight' => 100]);
    $cart2 = $this->createCart(1, ['stock' => 3, 'weight' => 100]);

    $this->attemptToCheckoutAndExpect422('cart', [$cart1->id, $cart2->id])
      ->assertJsonPath(
        'errors.cart.0',
        sprintf('The quantity [ID%s] must not be greater than stock.', $cart1->id)
      );
  }

  /** @test */
  public function cannot_checkout_if_total_weight_exceed_25kg()
  {
    $cart1 = $this->createCart(2, ['stock' => 5, 'weight' => 3000]);
    $cart2 = $this->createCart(2, ['stock' => 5, 'weight' => 10000]);

    $this->attemptToCheckoutAndExpect422('cart', [$cart1->id, $cart2->id])
      ->assertJsonPath(
        'errors.cart.0',
        'The total weight must not be greater than 25kg.'
      );
  }
}
