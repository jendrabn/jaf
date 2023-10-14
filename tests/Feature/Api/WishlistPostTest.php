<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\WishlistController;
use App\Http\Requests\Api\CreateWishlistRequest;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\Rule;
use Tests\TestCase;

class WishlistPostTest extends TestCase
{
  use RefreshDatabase;

  private string $uri = '/api/wishlist';

  /** @test */
  public function can_add_product_to_wishlist()
  {
    $this->seed(ProductCategorySeeder::class);

    $user = $this->createUser();
    $product = $this->createProduct();
    $data = ['product_id' => $product->id];

    $response1 = $this->postJson(
      $this->uri,
      $data,
      $this->authBearerToken($user)
    );

    $response1->assertCreated()
      ->assertExactJson(['data' => true]);

    $this->assertDatabaseCount('wishlists', 1)
      ->assertDatabaseHas('wishlists', ['user_id' => $user->id, ...$data]);

    $response2 = $this->postJson(
      $this->uri,
      $data,
      $this->authBearerToken($user)
    );

    $response2->assertCreated()
      ->assertExactJson(['data' => true]);

    $this->assertDatabaseCount('wishlists', 1)
      ->assertDatabaseHas('wishlists', ['user_id' => $user->id, ...$data]);
  }

  /** @test */
  public function unauthenticated_user_cannot_add_product_to_wishlist()
  {
    $response = $this->postJson($this->uri);

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

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
}
