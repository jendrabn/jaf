<?php

// tests/Feature/Api/RegionCityByProvinceGetTest.php

namespace Tests\Feature\Api;

use App\Models\City;
use Database\Seeders\CitySeeder;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegionCityByProvinceGetTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function can_get_cities_by_province_id()
  {
    $this->seed([ProvinceSeeder::class, CitySeeder::class]);
    $cities = City::where('province_id', 6)
      ->get()
      ->map(fn ($city) => $city->only(['id', 'type', 'name',]))
      ->toArray();

    $response = $this->getJson('/api/region/cities/' . 6);

    $response->assertOk()
      ->assertExactJson(['data' => $cities])
      ->assertJsonCount(6, 'data');
  }

  /** @test */
  public function returns_not_found_error_if_province_id_doenot_exist()
  {
    $this->seed([ProvinceSeeder::class]);

    $response = $this->getJson('/api/region/cities/' . 50);

    $response->assertNotFound()
      ->assertJsonStructure(['message']);
  }
}
