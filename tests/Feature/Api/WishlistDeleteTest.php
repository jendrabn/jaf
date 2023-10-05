<?php
// tests/Feature/Api/WishlistDeleteTest.php
namespace Tests\Feature\Api;

use App\Http\Controllers\Api\WishlistController;
use App\Http\Requests\Api\DeleteWishlistRequest;
use App\Models\Wishlist;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\Rule;
use Tests\TestCase;

class WishlistDeleteTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function delete_wishlists_uses_the_correct_form_request()
  {
    $this->assertActionUsesFormRequest(
      WishlistController::class,
      'delete',
      DeleteWishlistRequest::class
    );
  }

  /** @test */
  public function delete_wishlists_request_has_the_correct_validation_rules()
  {
    $user =  $this->createUser();
    $rules = (new DeleteWishlistRequest())
      ->setUserResolver(fn () => $user)
      ->rules();

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

  /** @test */
  public function unauthenticated_user_cannot_delete_wishlists()
  {
    $response = $this->deleteJson('/api/wishlist', ['Authorization' => 'Bearer Invalid-Token']);

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function can_delete_wishlists()
  {
    $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);

    $user = $this->createUser();
    $wishlists = Wishlist::factory()
      ->count(2)
      ->sequence(
        ['product_id' => $this->createProduct()->id],
        ['product_id' => $this->createProduct()->id],
      )
      ->for($user)
      ->create();

    $response = $this->deleteJson(
      '/api/wishlist',
      ['wishlist_ids' => $wishlists->pluck('id')],
      $this->authBearerToken($user)
    );

    $response->assertOk()
      ->assertExactJson(['data' => true]);

    $this->assertDatabaseEmpty('wishlists');
  }
}
