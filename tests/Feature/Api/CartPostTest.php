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
use Illuminate\Validation\Rule;
use Tests\TestCase;

class CartPostTest extends TestCase
{
  use RefreshDatabase;

  private User $user;

  protected function setUp(): void
  {
    parent::setUp();

    $this->seed(ProductCategorySeeder::class);
    $this->user = User::factory()->create();
  }

  private function attemptAddProductToCart(?array $data = [], ?string $authToken = null)
  {
    $authToken = $authToken
      ?? $this->user->createToken('auth_token')->plainTextToken;

    return $this->postJson('/api/carts', $data, ['Authorization' => 'Bearer ' . $authToken]);
  }

  /** @test */
  public function can_add_product_to_cart()
  {
    $product = Product::factory()->create(['stock' => 10]);

    $response = $this->attemptAddProductToCart([
      'product_id' => $product->id, 'quantity' => 2
    ]);

    $response = $this->attemptAddProductToCart([
      'product_id' => $product->id, 'quantity' => 3
    ]);

    $response->assertCreated()
      ->assertExactJson(['data' => true]);

    $this->assertDatabaseCount('carts', 1);
    $this->assertDatabaseHas('carts', [
      'user_id' => $this->user->id,
      'product_id' => $product->id,
      'quantity' => 5,
    ]);
  }

  /** @test */
  public function unauthenticated_user_cannot_add_product_to_cart()
  {
    $response = $this->attemptAddProductToCart(authToken: ' Invalid-Token');

    $response->assertUnauthorized()
      ->assertExactJson(['message' => 'Unauthenticated.']);
  }

  /** @test */
  public function cannot_add_product_to_cart_if_quantity_exceeds_stock()
  {
    $product = Product::factory()->create(['stock' => 5]);
    Cart::factory()
      ->for($product)
      ->for($this->user)
      ->create(['quantity' => 3]);

    $response = $this->attemptAddProductToCart([
      'product_id' => $product->id, 'quantity' => 3
    ]);

    $response->assertUnprocessable()
      ->assertJsonValidationErrorFor('cart');

    $this->assertDatabaseCount('carts', 1);
    $this->assertDatabaseHas('carts', [
      'user_id' => $this->user->id,
      'product_id' => $product->id,
      'quantity' => 3,
    ]);
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
        'required',
        'integer',
        Rule::exists('products', 'id')
          ->where('is_publish', true)
      ],
      'quantity' => [
        'required',
        'integer',
        'min:1'
      ]
    ], (new CreateCartRequest())->rules());
  }
}
