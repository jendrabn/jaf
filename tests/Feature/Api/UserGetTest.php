<?php

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

    $response = $this->getJson('/api/user', headers: [
      'Authorization' => 'Bearer ' . $user->createToken('auth_token')->plainTextToken
    ]);

    $response->assertOk()
      ->assertExactJson([
        'data' => [
          'id' => $user->id,
          'name' => $user->name,
          'email' => $user->email,
          'phone' => $user->phone,
          'sex' => $user->sex,
          'birth_date' => $user->birth_date,
        ]
      ]);
  }

  /** @test */
  public function returns_unauthenticated_error_if_user_is_not_authenticated()
  {
    $response = $this->getJson('/api/user', headers: [
      'Authorization' => 'Bearer wrong-token'
    ]);

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }
}
