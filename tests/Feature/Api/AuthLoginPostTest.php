<?php

// tests/Feature/Api/AuthLoginPostTest.php

namespace Tests\Feature\Api;

use App\Http\Requests\Api\LoginRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\Rule;
use Tests\TestCase;

class AuthLoginPostTest extends TestCase
{
  use RefreshDatabase;

  public string $loginRoute;

  protected function setUp(): void
  {
    parent::setUp();
    $this->loginRoute = '/api/auth/login';
    $this->seed(RolesAndPermissionsSeeder::class);
  }

  /** @test */
  public function login_uses_the_correct_form_request()
  {
    $this->assertActionUsesFormRequest(AuthController::class, 'login', LoginRequest::class);
  }

  /** @test */
  public function login_request_has_the_correct_rules()
  {
    $this->assertValidationRules(
      [
        'email' => [
          'required',
          'string',
        ],
        'password' => [
          'required',
          'string',
        ],
      ],
      (new LoginRequest())->rules()
    );
  }

  /** @test */
  public function can_login()
  {
    $user = $this->createUser(['password' => $password = 'seCret123']);

    $response = $this->postJson($this->loginRoute, [
      'email' => $user->email,
      'password' => $password,
    ]);

    $response->assertOk()
      ->assertJsonStructure([
        'data' => [
          'id',
          'name',
          'email',
          'phone',
          'sex',
          'birth_date',
          'auth_token',
        ]
      ])
      ->assertJson([
        'data' => $user->only(['id', 'name', 'email', 'phone', 'sex', 'birth_date',])
      ]);

    $this->assertCount(1, $user->tokens);
  }

  /** @test */
  public function returns_unauthenticated_error_if_email_doenot_exist()
  {
    $response = $this->postJson($this->loginRoute, [
      'email' => 'ghost@gmail.com',
      'password' => 'seCret123',
    ]);

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function returns_unauthenticated_error_if_password_is_incorrect()
  {
    $user = $this->createUser(['password' => 'seCret123']);

    $response = $this->postJson($this->loginRoute, [
      'email' => $user->email,
      'password' => 'seCret',
    ]);

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function returns_validation_error_if_all_fields_are_invalid()
  {
    $response = $this->postJson($this->loginRoute, [
      'email' => 'jcenagmail.com',
      'password' => '',
    ]);

    $response->assertUnprocessable()
      ->assertJsonValidationErrors(['email', 'password']);
  }
}
