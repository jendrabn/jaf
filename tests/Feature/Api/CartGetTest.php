<?php

namespace Tests\Feature\Api;

use App\Models\Cart;
use Database\Seeders\{ProductBrandSeeder, ProductCategorySeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CartGetTest extends TestCase
{
  use RefreshDatabase;

  const URI = '/api/carts';

  /** @test */
  public function can_get_all_carts()
  {
    $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);

    $user = $this->createUser();
    $carts = Cart::factory(3)
      ->sequence(
        ['product_id' => $this->createProduct()->id],
        ['product_id' => $this->createProduct()->id],
        ['product_id' => $this->createProduct()->id],
      )
      ->for($user)
      ->create();

    $response = $this->getJson(self::URI, $this->authBearerToken($user));

    $response->assertOk()
      ->assertExactJson([
        'data' => $carts->sortByDesc('id')->values()->map(
          fn ($cart) => [
            'id' => $cart->id,
            'product' => $this->formatProductData($cart->product),
            'quantity' => $cart->quantity,
          ]
        )->toArray()
      ]);
  }

  /** @test */
  public function unauthenticated_user_cannot_get_all_carts()
  {
    $response = $this->getJson(self::URI);

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }
}
