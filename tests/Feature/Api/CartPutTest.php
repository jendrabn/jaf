<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\CartController;
use App\Http\Requests\Api\UpdateCartRequest;
use App\Models\{Cart, User};
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CartPutTest extends TestCase
{
  use RefreshDatabase;

  private User $user;

  protected function setUp(): void
  {
    parent::setUp();
    $this->seed(ProductCategorySeeder::class);
    $this->user = $this->createUser();
  }

  private function uri(int $id = 1): string
  {
    return '/api/carts/' . $id;
  }

  /** @test */
  public function can_update_cart()
  {
    $cart = Cart::factory()
      ->for($this->createProduct(['stock' => 2]))
      ->for($this->user)
      ->create(['quantity' => 1]);
    $data = ['quantity' => 2];

    $response = $this->putJson(
      $this->uri($cart->id),
      $data,
      $this->authBearerToken($this->user)
    );

    $response->assertOk()
      ->assertExactJson(['data' => true]);

    $this->assertDatabaseCount('carts', 1)
      ->assertDatabaseHas('carts', $data);
  }

  /** @test */
  public function unauthenticated_user_cannot_update_cart()
  {
    $response = $this->putJson($this->uri());

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function cannot_update_cart_if_quantity_exceeds_stock()
  {
    $cart = Cart::factory()
      ->for($this->createProduct(['stock' => 1]))
      ->for($this->user)
      ->create(['quantity' => 1]);

    $response = $this->putJson(
      $this->uri($cart->id),
      ['quantity' => 2],
      $this->authBearerToken($this->user)
    );

    $response->assertUnprocessable()
      ->assertJsonValidationErrors(['cart']);

    $this->assertDatabaseCount('carts', 1)
      ->assertModelExists($cart);
  }

  /** @test */
  public function return_not_found_error_if_cart_id_doenot_exists()
  {
    $cart = Cart::factory()
      ->for($this->createProduct())
      ->for($this->user)
      ->create();

    $response = $this->putJson(
      $this->uri($cart->id + 1),
      ['quantity' => 1],
      $this->authBearerToken($this->user)
    );

    $response->assertNotFound()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function update_cart_uses_the_correct_form_request()
  {
    $this->assertActionUsesFormRequest(
      CartController::class,
      'update',
      UpdateCartRequest::class
    );
  }

  /** @test */
  public function update_cart_request_has_the_correct_validation_rules()
  {
    $this->assertValidationRules([
      'quantity' => [
        'required', 'integer', 'min:1'
      ]
    ], (new UpdateCartRequest())->rules());
  }
}
