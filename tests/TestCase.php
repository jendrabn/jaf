<?php

namespace Tests;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use JMac\Testing\Traits\AdditionalAssertions;

abstract class TestCase extends BaseTestCase
{
  use CreatesApplication, AdditionalAssertions;

  protected function createUser(?array $data = [], int $count = 1, string $role = 'user'): User|Collection
  {
    $users = User::factory()->count($count)->create($data);

    $users->each(fn ($user) => $user->assignRole($role));

    return $count > 1 ? $users : $users->first();
  }
}
