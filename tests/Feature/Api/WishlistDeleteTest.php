<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\WishlistController;
use App\Http\Requests\Api\DeleteWishlistRequest;
use App\Models\Wishlist;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\Rule;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class WishlistDeleteTest extends TestCase
{
  use RefreshDatabase;

  #[Test]
  public function delete_wishlist_uses_the_correct_form_request()
  {
    $this->assertActionUsesFormRequest(
      WishlistController::class,
      'delete',
      DeleteWishlistRequest::class
    );
  }

  #[Test]
  public function delete_wishlist_request_has_the_correct_validation_rules()
  {
    $user = $this->createUser();
    $rules = (new DeleteWishlistRequest())->setUserResolver(fn () => $user)->rules();

    $this->assertValidationRules([
      'wishlist_ids' => [
        'required',
        'array'
      ],
      'wishlist_ids.*' => [
        'required',
        'integer',
        Rule::exists('wishlists', 'id')->where('user_id', $user->id)
      ]
    ], $rules);
  }

  #[Test]
  public function unauthenticated_user_cannot_delete_wishlist()
  {
    $response = $this->deleteJson('/api/wishlist');

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

  #[Test]
  public function can_delete_wishlist()
  {
    $this->seed(ProductCategorySeeder::class);

    $user = $this->createUser();
    $wishlists = Wishlist::factory(2)
      ->sequence(
        ['product_id' => $this->createProduct()->id],
        ['product_id' => $this->createProduct()->id],
      )
      ->for($user)
      ->create();

    $response = $this->deleteJson('/api/wishlist', [
      'wishlist_ids' => $wishlists->pluck('id')
    ], $this->authBearerToken($user));

    $response->assertOk()
      ->assertExactJson(['data' => true]);

    $this->assertDatabaseEmpty('wishlists');
  }
}
