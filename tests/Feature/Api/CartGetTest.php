<?php

namespace Tests\Feature\Api;

use App\Models\Cart;
use Database\Seeders\{ProductBrandSeeder, ProductCategorySeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartGetTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function unauthenticated_user_cannot_get_all_carts()
  {
    $response = $this->getJson('/api/carts');

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

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

    $expectedCarts =  $carts->sortByDesc('id')->values();

    $response = $this->getJson('/api/carts', $this->authBearerToken($user));

    $response->assertOk()
      ->assertExactJson([
        'data' => $expectedCarts->map(fn ($item) => [
          'id' => $item->id,
          'product' => $this->formatProductData($item->product),
          'quantity' => $item->quantity,
        ])->toArray()
      ]);
  }
}
