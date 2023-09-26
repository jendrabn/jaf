<?php

// tests\Feature\Api\UserGetTest.php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserGetTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function can_get_user_profile()
  {
    $user = $this->createUser();

    $response = $this->getJson('/api/user', headers: $this->authBearerToken($user));

    $response->assertOk()
      ->assertExactJson([
        'data' => [
          'id' => $user->id,
          'name' => $user->name,
          'email' => $user->email,
          'phone' => $user->phone,
          'sex' => (string) $user->sex,
          'birth_date' => $user->birth_date,
        ]
      ]);
  }

  /** @test */
  public function returns_unauthenticated_error_if_user_is_not_authenticated()
  {
    $response = $this->getJson('/api/user');

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }
}
