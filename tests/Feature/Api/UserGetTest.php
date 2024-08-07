<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UserGetTest extends TestCase
{
  use RefreshDatabase;

  #[Test]
  public function unauthenticated_user_cannot_get_profile()
  {
    $response = $this->putJson('/api/user');

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

  #[Test]
  public function can_get_profile()
  {
    $user = $this->createUser();

    $response = $this->getJson('/api/user', headers: $this->authBearerToken($user));

    $response->assertOk()
      ->assertExactJson(['data' => $this->formatUserData($user)]);
  }
}
