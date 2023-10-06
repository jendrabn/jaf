<?php
// tests\Feature\Api\CartPostTest.php
namespace Tests\Feature\Api;

use App\Http\Controllers\Api\CartController;
use App\Http\Requests\Api\CreateCartRequest;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CartPostTest extends TestCase
{
  use RefreshDatabase;

  private User $user;

  protected function setUp(): void
  {
    parent::setUp();
    $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);
    $this->user = User::factory()->create();
  }

  private function attemptAddToCart(?array $data = [], ?array $headers = [])
  {
    $headers = array_merge($this->authBearerToken($this->user), $headers);

    return $this->postJson('/api/carts', $data, $headers);
  }

  /** @test */
  public function can_add_product_to_cart()
  {
    // Arrange
    $product = Product::factory()->create(['stock' => 10]);
    $data = ['product_id' => $product->id, 'quantity' => 3];

    // Action
    $response1 = $this->attemptAddToCart($data);

    // Assert
    $response1->assertCreated()
      ->assertExactJson(['data' => true]);
    $this->assertDatabaseHas('carts', $data + ['user_id' => $this->user->id]);

    // Action
    $response2 = $this->attemptAddToCart($data);

    // Assert
    $response2->assertCreated()
      ->assertExactJson(['data' => true]);
    $this->assertDatabaseCount('carts', 1);
    $this->assertDatabaseHas('carts', [
      'product_id' => $product->id,
      'quantity' => 6,
      'user_id' => $this->user->id
    ]);
  }

  /** @test */
  public function unauthenticated_user_cannot_add_product_to_cart()
  {
    $response = $this->attemptAddToCart(
      headers: ['Authorization' => 'Bearer Invalid-Token']
    );

    $response->assertUnauthorized()
      ->assertExactJson(['message' => 'Unauthenticated.']);
  }

  /** @test */
  public function cannot_add_product_to_cart_if_product_is_not_published()
  {
    $product = Product::factory()->create(['is_publish' => false]);

    $response = $this->attemptAddToCart(
      ['product_id' => $product->id, 'quantity' => 1]
    );

    $response->assertUnprocessable()
      ->assertJsonValidationErrorFor('product');
    $this->assertDatabaseEmpty('carts');
  }

  /** @test */
  public function cannot_add_product_to_cart_if_quantity_exceeds_stock()
  {
    $product = Product::factory()->create(['stock' => 5]);
    $cart = Cart::factory()->for($product)->for($this->user)->create(['quantity' => 3]);

    $response = $this->attemptAddToCart(
      ['product_id' => $product->id, 'quantity' => 3]
    );

    $response->assertUnprocessable()
      ->assertJsonValidationErrorFor('cart');
    $this->assertDatabaseHas('carts',  ['product_id' => $product->id, 'quantity' => 3]);
  }

  /** @test */
  public function create_cart_uses_the_correct_form_request()
  {
    $this->assertActionUsesFormRequest(
      CartController::class,
      'create',
      CreateCartRequest::class
    );
  }

  /** @test */
  public function create_cart_request_has_the_correct_validation_rules()
  {
    $this->assertValidationRules([
      'product_id' => [
        'required', 'integer', 'exists:products,id'
      ],
      'quantity' => [
        'required', 'integer', 'min:1'
      ]
    ], (new CreateCartRequest())->rules());
  }
}
