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
  public function can_delete_wishlist()
  {
    $this->seed(ProductCategorySeeder::class);

    $user = $this->createUser();
    $wishlist = Wishlist::factory()->count(2)->sequence(
      ['product_id' => $this->createProduct()->id],
      ['product_id' => $this->createProduct()->id],
    )
      ->for($user)
      ->create();
    $data =  ['wishlist_ids' => $wishlist->pluck('id')];

    $response = $this->deleteJson('/api/wishlist', $data, $this->authBearerToken($user));

    $response->assertOk()
      ->assertExactJson(['data' => true]);

    $this->assertDatabaseEmpty('wishlists');
  }

  /** @test */
  public function unauthenticated_user_cannot_delete_wishlist()
  {
    $response = $this->deleteJson(
      '/api/wishlist',
      ['Authorization' => 'Bearer Invalid-Token']
    );

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function delete_wishlist_uses_the_correct_form_request()
  {
    $this->assertActionUsesFormRequest(
      WishlistController::class,
      'delete',
      DeleteWishlistRequest::class
    );
  }

  /** @test */
  public function delete_wishlist_request_has_the_correct_validation_rules()
  {
    $user = $this->createUser();
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
        Rule::exists('wishlists', 'id')
          ->where('user_id', $user->id)
      ]
    ], $rules);
  }
}
