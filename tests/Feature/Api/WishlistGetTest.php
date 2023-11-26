<?php

namespace Tests\Feature\Api;

use App\Models\Wishlist;
use Database\Seeders\{ProductBrandSeeder, ProductCategorySeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistGetTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function unauthenticated_user_cannot_get_all_wishlist()
  {
    $response = $this->getJson('/api/wishlist');

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function can_get_all_wishlist()
  {
    $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);

    $user = $this->createUser();

    Wishlist::factory()
      ->for($this->createProduct(['is_publish' => false]))
      ->for($user)
      ->create();

    $wishlists = Wishlist::factory(3)
      ->sequence(
        ['product_id' => $this->createProduct()->id],
        ['product_id' => $this->createProduct()->id],
        ['product_id' => $this->createProduct()->id],
      )
      ->for($user)
      ->create();

    $expectedWishlists = $wishlists->sortByDesc('id')->values();

    $response = $this->getJson('/api/wishlist', $this->authBearerToken($user));

    $response->assertOk()
      ->assertExactJson([
        'data' => $expectedWishlists->map(fn ($item) => [
          'id' => $item->id,
          'product' => $this->formatProductData($item->product)
        ])->toArray()
      ])->assertJsonCount(3, 'data');
  }
}
