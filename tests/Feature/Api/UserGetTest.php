<?php

// tests\Feature\Api\UserGetTest.php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserGetTest extends TestCase
{
  use RefreshDatabase;

  private string $uri = '/api/user';

  /** @test */
  public function can_get_user_profile()
  {
    $user = $this->createUser();

    $response = $this->getJson($this->uri, headers: $this->authBearerToken($user));

    $response->assertOk()
      ->assertExactJson([
        'data' => $this->formatUserData($user)
      ]);
  }

  /** @test */
  public function returns_unauthenticated_error_if_user_is_not_authenticated()
  {
    $response = $this->getJson($this->uri);

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }
}
