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

  private string $uri = '/api/wishlist';

  /** @test */
  public function can_get_all_wishlist()
  {
    $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);

    $user = $this->createUser();
    Wishlist::factory()
      ->for($this->createProduct(['is_publish' => false]))
      ->for($user)
      ->create();
    $wishlists = Wishlist::factory(count: 3)
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
        'data' => $wishlists->sortByDesc('id')->values()->map(fn ($item) => [
          'id' => $item->id,
          'product' => $this->formatProductData($item->product)
        ])->toArray()
      ])
      ->assertJsonCount(3, 'data');
  }

  /** @test */
  public function unauthenticated_user_cannot_get_all_wishlist()
  {
    $response = $this->getJson($this->uri);

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }
}
