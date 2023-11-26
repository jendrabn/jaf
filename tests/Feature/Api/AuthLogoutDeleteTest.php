<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLogoutDeleteTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function unauthenticated_user_cannot_logout()
  {
    $response = $this->deleteJson('/api/auth/logout');

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function can_logout()
  {
    $user = $this->createUser();

    $response = $this->deleteJson('/api/auth/logout', headers: $this->authBearerToken($user));

    $response->assertOk()
      ->assertExactJson(['data' => true]);

    $this->assertCount(0, $user->fresh()->tokens);
  }
}
