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

  private string $uri = '/api/user';

  /** @test */
  public function update_profile_uses_the_correct_form_request()
  {
    $this->assertActionUsesFormRequest(UserController::class, 'update', ProfileRequest::class);
  }

  /** @test */
  public function profile_request_has_the_correct_rules()
  {
    $user = $this->createUser();
    $rules = (new ProfileRequest())
      ->setUserResolver(fn () => $user)
      ->rules();

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

  /** @test */
  public function returns_unauthenticated_error_if_user_is_not_authenticated()
  {
    $response = $this->putJson($this->uri);

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function can_update_profile()
  {
    $user = $this->createUser();
    $data = [
      'name' => 'Elon Musk',
      'email' => 'musk@gmail.com',
      'phone' => '087991776171',
      'sex' => 1,
      'birth_date' => '1970-09-26'
    ];

    $response = $this->putJson($this->uri, $data, $this->authBearerToken($user));

    $response->assertOk()
      ->assertExactJson([
        'data' => [
          'id' => $user->id,
          ...$data
        ]
      ]);
  }

  /** @test */
  public function returns_validation_error_if_all_fields_are_invalid()
  {
    $user = $this->createUser();
    $data = [
      'name' => '',
      'email' => 'muskgmail.com',
      'phone' => '097991776171',
      'sex' => 3,
      'birth_date' => '1970'
    ];

    $response = $this->putJson($this->uri, $data, $this->authBearerToken($user));

    $response->assertUnprocessable()
      ->assertJsonValidationErrors(array_keys($data));
  }
}
