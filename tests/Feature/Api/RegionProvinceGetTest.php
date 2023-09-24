<?php

// tests/Feature/Api/RegionProvinceGetTest.php

namespace Tests\Feature\Api;

use App\Models\Province;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegionProvinceGetTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function can_get_all_provinces()
  {
    $this->seed(ProvinceSeeder::class);

    $provinces = Province::all()
      ->map(fn ($province) => $province->only(['id', 'name']))
      ->toArray();

    $response = $this->getJson('/api/region/provinces');

    $response->assertOk()
      ->assertExactJson(['data' => $provinces])
      ->assertJsonCount(34, 'data');
  }
}
