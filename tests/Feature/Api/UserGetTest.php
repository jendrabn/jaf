<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserGetTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function unauthenticated_user_cannot_get_profile()
  {
    $response = $this->putJson('/api/user');

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function can_get_profile()
  {
    $user = $this->createUser();

    $response = $this->getJson('/api/user', headers: $this->authBearerToken($user));

    $response->assertOk()
      ->assertExactJson(['data' => $this->formatUserData($user)]);
  }
}
