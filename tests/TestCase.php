<?php

namespace Tests;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use JMac\Testing\Traits\AdditionalAssertions;

abstract class TestCase extends BaseTestCase
{
  use CreatesApplication, AdditionalAssertions;

  protected function createUser(?array $data = [], int $count = 1): User|Collection
  {
    $users = User::factory()->count($count)->create($data);

    return $count > 1 ? $users : $users->first();
  }

  protected function authBearerToken(User $user, ?bool $header = true): array|string
  {
    $token = $user->createToken('auth_token')->plainTextToken;

    return $header ? ['Authorization' => 'Bearer ' . $token] : $token;
  }
}
