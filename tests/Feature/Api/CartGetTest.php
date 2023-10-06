<?php
// tests/Feature/Api/CartGetTest.php
namespace Tests\Feature\Api;

use App\Models\Cart;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CartGetTest extends TestCase
{
  use RefreshDatabase;

  private string $uri = '/api/carts';

  /** @test */
  public function unauthenticated_user_cannot_add_product_to_cart()
  {
    $response = $this->getJson($this->uri,  ['Authorization' => 'Bearer Invalid-Token']);

    $response->assertUnauthorized()
      ->assertExactJson(['message' => 'Unauthenticated.']);
  }

  /** @test */
  public function can_get_all_carts()
  {
    $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);

    $user = $this->createUser();
    $carts = Cart::factory()
      ->count(3)
      ->sequence(
        ['product_id' => $this->createProduct()->id],
        ['product_id' => $this->createProduct()->id],
        ['product_id' => $this->createProduct()->id],
      )
      ->for($user)
      ->create()
      ->sortByDesc('id')
      ->values();

    $response = $this->getJson($this->uri, $this->authBearerToken($user));

    $response->assertOk()
      ->assertExactJson([
        'data' => $carts->map(
          fn ($cart) => [
            'id' => $cart->id,
            'product' => $this->formatProductData($cart->product),
            'quantity' => $cart->quantity,
          ]
        )->toArray()
      ]);
  }
}
