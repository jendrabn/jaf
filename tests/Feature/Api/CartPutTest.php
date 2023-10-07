<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\CartController;
use App\Http\Requests\Api\CreateCartRequest;
use App\Http\Requests\Api\UpdateCartRequest;
use App\Models\Cart;
use App\Models\User;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CartPutTest extends TestCase
{
  use RefreshDatabase;

  private string $uri = '/api/carts/';
  private User $user;
  private Cart $cart;

  protected function setUp(): void
  {
    parent::setUp();
    $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);
    $this->user = $this->createUser();
    $this->cart = Cart::factory()
      ->for($this->createProduct(['stock' => 5]))
      ->for($this->user)
      ->create(['quantity' => 3]);
  }

  private function attemptToUpdateCart(array $data = ['quantity' => 1], ?int $cartId = 1, ?array $headers = [])
  {
    $headers =  array_merge($this->authBearerToken($this->user), $headers);

    $response = $this->putJson(
      $this->uri . $cartId,
      $data,
      $headers
    );

    return $response;
  }

  /** @test */
  public function can_update_cart()
  {
    $response = $this->attemptToUpdateCart();
    $response->assertOk()
      ->assertExactJson(['data' => true]);
    $this->assertDatabaseCount('carts', 1);
    $this->assertDatabaseHas('carts', ['id' => $this->cart->id, 'quantity' => 4]);
  }

  /** @test */
  public function unauthenticated_user_cannot_update_cart()
  {
    $response = $this->attemptToUpdateCart(headers: ['Authorization' => 'Bearer Invalid-Token']);
    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function cannot_update_cart_if_quantity_exceeds_stock()
  {
    $response = $this->attemptToUpdateCart(['quantity' => 3]);
    $response->assertUnprocessable()
      ->assertJsonValidationErrorFor('cart');
  }

  /** @test */
  public function return_not_found_error_if_cart_id_doenot_exists()
  {
    $response = $this->attemptToUpdateCart(cartId: $this->cart->id + 1);
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
