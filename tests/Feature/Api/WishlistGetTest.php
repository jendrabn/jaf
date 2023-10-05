<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WishlistGetTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function unauthenticated_user_cannot_get_all_wish_lists()
  {
    $response = $this->getJson('/api/wishlist', ['Authorization' => 'Bearer Invalid-Token']);

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function can_get_all_wishlists()
  {
    $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);

    $user = $this->createUser();
    $wishlists = Wishlist::factory()
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

    Wishlist::factory()
      ->for(Product::factory()->create(['is_publish' => false]))
      ->for($user)
      ->create();

    $response = $this->getJson('/api/wishlist', $this->authBearerToken($user));

    $response->assertOk()
      ->assertExactJson([
        'data' => $wishlists->map(fn ($item) => [
          'id' => $item->id,
          'product' => $this->formatProductData($item->product),
        ])->toArray()
      ])
      ->assertJsonCount(3, 'data');
  }
}
