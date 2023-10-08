<?php
// tests/Feature/Api/AuthLogoutDeleteTest.php
namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthLogoutDeleteTest extends TestCase
{
  use RefreshDatabase;

  private string $uri = '/api/auth/logout';

  /** @test */
  public function can_logout()
  {
    $user = $this->createUser();

    $response = $this->deleteJson($this->uri, headers: $this->authBearerToken($user));

    $response->assertOk()
      ->assertExactJson(['data' => true]);

    $this->assertEquals(0, $user->fresh()->tokens->count());
  }

  /** @test */
  public function returns_unauthenticated_error_if_user_is_not_authenticated()
  {
    $response = $this->deleteJson($this->uri);

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }
}
