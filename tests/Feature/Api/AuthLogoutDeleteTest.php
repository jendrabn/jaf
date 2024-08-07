<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AuthLogoutDeleteTest extends TestCase
{
  use RefreshDatabase;

  #[Test]
  public function unauthenticated_user_cannot_logout()
  {
    $response = $this->deleteJson('/api/auth/logout');

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

  #[Test]
  public function can_logout()
  {
    $user = $this->createUser();

    $response = $this->deleteJson('/api/auth/logout', headers: $this->authBearerToken($user));

    $response->assertOk()
      ->assertExactJson(['data' => true]);

    $this->assertCount(0, $user->fresh()->tokens);
  }
}
