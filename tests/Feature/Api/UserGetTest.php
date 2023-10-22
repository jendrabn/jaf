<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserGetTest extends TestCase
{
  use RefreshDatabase;

  const URI = '/api/user';

  /** @test */
  public function can_get_profile()
  {
    $user = $this->createUser();

    $response = $this->getJson(self::URI, headers: $this->authBearerToken($user));

    $response->assertOk()
      ->assertExactJson(['data' => $this->formatUserData($user)]);
  }

  /** @test */
  public function unauthenticated_user_cannot_get_profile()
  {
    $response = $this->putJson(self::URI);

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }
}
