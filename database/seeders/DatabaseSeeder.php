<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    // \App\Models\User::factory(10)->create();

    // \App\Models\User::factory()->create([
    //     'name' => 'Test User',
    //     'email' => 'test@example.com',
    // ]);

    $this->call([
      RolesAndPermissionsSeeder::class,
      ProvinceSeeder::class,
      CitySeeder::class,
      ProductCategorySeeder::class,
      ProductBrandSeeder::class,
      BankSeeder::class
    ]);

    User::create([
      'name' => 'Admin',
      'email' => 'admin@mail.com',
      'password' => 'Secret123',
    ])->assignRole(User::ROLE_ADMIN);

    User::create([
      'name' => 'User',
      'email' => 'user@mail.com',
      'password' => 'Secret123',
    ])->assignRole(User::ROLE_USER);
  }
}
