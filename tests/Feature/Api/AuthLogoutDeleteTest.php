<?php

namespace Tests\Feature\Api;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthLogoutDeleteTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function can_logout()
  {
    $this->seed(RolesAndPermissionsSeeder::class);
    $user = $this->createUser();

    $response = $this->deleteJson('/api/auth/logout', headers: [
      'Authorization' => 'Bearer ' . $user->createToken('auth_token')->plainTextToken
    ]);

    $response->assertOk()
      ->assertExactJson(['data' => true]);
  }

  /** @test */
  public function returns_unauthenticated_error_if_user_is_not_authenticated()
  {
    $response = $this->deleteJson('/api/auth/logout', headers: [
      'Authorization' => 'Bearer wrong-token'
    ]);

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }
}
