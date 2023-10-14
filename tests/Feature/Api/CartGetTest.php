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

  private string $uri = '/api/carts';

  /** @test */
  public function can_get_all_carts()
  {
    $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);

    $user = $this->createUser();
    $carts = Cart::factory(count: 3)
      ->sequence(
        ['product_id' => $this->createProduct()->id],
        ['product_id' => $this->createProduct()->id],
        ['product_id' => $this->createProduct()->id],
      )
      ->for($user)
      ->create();

    $response = $this->getJson($this->uri, $this->authBearerToken($user));

    $response->assertOk()
      ->assertExactJson([
        'data' => $carts->sortByDesc('id')
          ->values()
          ->map(fn ($item) => [
            'id' => $item->id,
            'product' => $this->formatProductData($item->product),
            'quantity' => $item->quantity,
          ])
          ->toArray()
      ]);
  }

  /** @test */
  public function unauthenticated_user_cannot_get_all_carts()
  {
    $response = $this->getJson($this->uri);

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }
}
