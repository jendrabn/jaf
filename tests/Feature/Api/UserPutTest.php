<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\UserController;
use App\Http\Requests\Api\ProfileRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\Rule;
use Tests\TestCase;

class UserPutTest extends TestCase
{
  use RefreshDatabase;

  const URI = '/api/user';

  /** @test */
  public function can_update_profile()
  {
    $user = $this->createUser();
    $data = [
      'name' => 'Ali',
      'email' => 'ali@gmail.com',
      'phone' => '087991776171',
      'sex' => 1,
      'birth_date' => fake()->date
    ];

    $response = $this->putJson(self::URI, $data, $this->authBearerToken($user));

    $response->assertOk()
      ->assertExactJson(['data' => ['id' => $user->id, ...$data]]);

    $this->assertDatabaseHas('users', $data);
  }

  /** @test */
  public function unauthenticated_user_cannot_update_profile()
  {
    $response = $this->putJson(self::URI);

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function update_profile_uses_the_correct_form_request()
  {
    $this->assertActionUsesFormRequest(
      UserController::class,
      'update',
      ProfileRequest::class
    );
  }

  /** @test */
  public function profile_request_has_the_correct_rules()
  {
    $user = $this->createUser();
    $rules = (new ProfileRequest())->setUserResolver(fn () => $user)->rules();

    $this->assertValidationRules(
      [
        'name' => [
          'required',
          'string',
          'min:1',
          'max:30',
        ],
        'email' => [
          'required',
          'string',
          'email',
          'min:1',
          'max:255',
          Rule::unique('users', 'email')->ignore($user->id)
        ],
        'phone' => [
          'nullable',
          'string',
          'min:10',
          'max:15',
          'starts_with:08,62,+62',
        ],
        'sex' => [
          'nullable',
          'integer',
          Rule::in([1, 2])
        ],
        'birth_date' => [
          'nullable',
          'string',
          'date',
        ],
      ],
      $rules
    );
  }
}
