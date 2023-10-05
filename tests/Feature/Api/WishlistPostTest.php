<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\WishlistController;
use App\Http\Requests\Api\CreateWishlistRequest;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\Rule;
use Tests\TestCase;

class WishlistPostTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function create_wishlist_uses_the_correct_form_request()
  {
    $this->assertActionUsesFormRequest(
      WishlistController::class,
      'create',
      CreateWishlistRequest::class
    );
  }

  /** @test */
  public function create_wishlist_request_has_the_correct_validation_rules()
  {
    $this->assertValidationRules([
      'product_id' => [
        'required',
        'integer',
        Rule::exists('products', 'id')->where('is_publish', true)
      ]
    ], (new CreateWishlistRequest())->rules());
  }

  /** @test */
  public function unauthenticated_user_cannot_create_wishlist()
  {
    $response = $this->postJson('/api/wishlist', ['Authorization' => 'Bearer Invalid-Token']);

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function can_create_wishlist()
  {
    $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);
    $user = $this->createUser();
    $product = $this->createProduct();

    $response = $this->postJson(
      '/api/wishlist',
      ['product_id' => $product->id],
      $this->authBearerToken($user)
    );

    $response->assertCreated()
      ->assertExactJson(['data' => true]);
    $this->assertDatabaseHas('wishlists', ['product_id' => $product->id]);
    $this->assertDatabaseCount('wishlists', 1);

    $response = $this->postJson(
      '/api/wishlist',
      ['product_id' => $product->id],
      $this->authBearerToken($user)
    );
    $this->assertDatabaseCount('wishlists', 1);
  }
}
