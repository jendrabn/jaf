<?php

namespace Tests\Feature\Api;

use App\Models\Wishlist;
use Database\Seeders\{ProductBrandSeeder, ProductCategorySeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WishlistGetTest extends TestCase
{
  use RefreshDatabase;

  const URI = '/api/wishlist';

  /** @test */
  public function can_get_all_wishlist()
  {
    $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);

    $user = $this->createUser();
    Wishlist::factory()
      ->for($this->createProduct(['is_publish' => false]))
      ->for($user)
      ->create();
    $wishlist = Wishlist::factory(3)
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
        'data' => $wishlist->sortByDesc('id')->values()->map(
          fn ($item) => [
            'id' => $item->id,
            'product' => $this->formatProductData($item->product)
          ]
        )->toArray()
      ])
      ->assertJsonCount(3, 'data');
  }

  /** @test */
  public function unauthenticated_user_cannot_get_all_wishlist()
  {
    $response = $this->getJson(self::URI);

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }
}
