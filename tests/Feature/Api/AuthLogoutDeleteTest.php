<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthLogoutDeleteTest extends TestCase
{
  use RefreshDatabase;

  const URI = '/api/auth/logout';

  /** @test */
  public function can_logout()
  {
    $user = $this->createUser();

    $response = $this->deleteJson(self::URI, headers: $this->authBearerToken($user));

    $response->assertOk()
      ->assertExactJson(['data' => true]);

    $this->assertCount(0, $user->fresh()->tokens);
  }

  /** @test */
  public function unauthenticated_user_cannot_logout()
  {
    $response = $this->deleteJson(self::URI);

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }
}
