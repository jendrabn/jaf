<?php

// tests/Feature/Api/AuthLogoutDeleteTest.php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthLogoutDeleteTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function can_logout()
  {
    $user = $this->createUser();

    $response = $this->deleteJson('/api/auth/logout', [], $this->authBearerToken($user));

    $response->assertOk()
      ->assertExactJson(['data' => true]);

    $this->assertEquals(0, $user->tokens->count());
  }

  /** @test */
  public function returns_unauthenticated_error_if_user_is_not_authenticated()
  {
    $response = $this->deleteJson('/api/auth/logout');

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }
}
