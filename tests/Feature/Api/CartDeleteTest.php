<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\CartController;
use App\Http\Requests\Api\DeleteCartRequest;
use App\Models\Cart;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\Rule;
use Tests\TestCase;

class CartDeleteTest extends TestCase
{
  use RefreshDatabase;

  private string $uri = '/api/carts';

  /** @test */
  public function unauthenticated_user_cannot_add_product_to_cart()
  {
    $response = $this->deleteJson($this->uri,  ['Authorization' => 'Bearer Invalid-Token']);

    $response->assertUnauthorized()
      ->assertExactJson(['message' => 'Unauthenticated.']);
  }

  /** @test */
  public function can_delete_carts()
  {
    $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);

    $user = $this->createUser();
    $carts = Cart::factory()
      ->count(3)
      ->sequence(
        ['product_id' => $this->createProduct()->id],
        ['product_id' => $this->createProduct()->id],
        ['product_id' => $this->createProduct()->id],
      )
      ->for($user)
      ->create();
    Cart::factory()->for($this->createUser())->create();
    $cartIds = $carts->pluck('id')->toArray();

    $response = $this->deleteJson($this->uri, ['cart_ids' => $cartIds], $this->authBearerToken($user));

    $response->assertOk()
      ->assertExactJson(['data' => true]);
    $this->assertDatabaseCount('carts', 1);
  }

  /** @test */
  public function delete_cart_uses_the_correct_form_request()
  {
    $this->assertActionUsesFormRequest(
      CartController::class,
      'delete',
      DeleteCartRequest::class
    );
  }

  /** @test */
  public function delete_cart_request_has_the_correct_validation_rules()
  {
    $user = $this->createUser();
    $rules = (new DeleteCartRequest())
      ->setUserResolver(fn () => $user)
      ->rules();

    $this->assertValidationRules([
      'cart_ids' => [
        'required',
        'array'
      ],
      'cart_ids.*' => [
        'required',
        'integer',
        Rule::exists('carts', 'id')->where('user_id', $user->id)
      ]
    ], $rules);
  }
}
