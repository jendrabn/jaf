<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\CartController;
use App\Http\Requests\Api\CreateCartRequest;
use App\Models\{Cart, Product, User};
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\Rule;
use Tests\TestCase;

class CartPostTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function can_add_product_to_cart()
  {
    // Arrange
    $this->seed(ProductCategorySeeder::class);

    $product = Product::factory()->create(['stock' => 10]);
    $user = User::factory()->create();
    $authToken = $user->createToken('auth_token')->plainTextToken;
    $headers = ['Authorization' => 'Bearer ' . $authToken];
    $data = [
      'product_id' => $product->id,
      'quantity' => 3
    ];

    // Act
    $response1 = $this->postJson('/api/carts', $data, $headers);

    // Assert
    $response1->assertCreated()
      ->assertExactJson(['data' => true]);

    $this->assertDatabaseCount('carts', 1)
      ->assertDatabaseHas('carts', ['user_id' => $user->id, ...$data]);

    // Act
    $response2 = $this->postJson('/api/carts', $data, $headers);

    // Assert
    $response2->assertCreated()
      ->assertExactJson(['data' => true]);

    $this->assertDatabaseCount('carts', 1)
      ->assertDatabaseHas('carts',  ['quantity' => $data['quantity'] * 2]);
  }

  /** @test */
  public function unauthenticated_user_cannot_add_product_to_cart()
  {
    $response = $this->postJson('/api/carts');

    $response->assertUnauthorized()
      ->assertExactJson(['message' => 'Unauthenticated.']);
  }

  /** @test */
  public function cannot_add_product_to_cart_if_quantity_exceeds_stock()
  {
    $this->seed(ProductCategorySeeder::class);

    $product = Product::factory()->create(['stock' => 5]);
    $user = User::factory()->create();
    $cart = Cart::factory()->for($product)->for($user)->create(['quantity' => 3]);

    $response = $this->postJson('/api/carts', [
      'product_id' => $product->id,
      'quantity' => 3
    ], $this->authBearerToken($user));

    $response->assertUnprocessable()
      ->assertJsonValidationErrors(['quantity']);

    $this->assertDatabaseCount('carts', 1)
      ->assertModelExists($cart);
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
