<?php

namespace Tests\Feature\Api;

use App\Models\City;
use Database\Seeders\{CitySeeder, ProvinceSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegionCityGetTest extends TestCase
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();
    $this->seed(ProvinceSeeder::class);
  }

  private function uri(int $provinceId = 1): string
  {
    return '/api/region/cities/' . $provinceId;
  }

  /** @test */
  public function can_get_cities_by_province_id()
  {
    $this->seed(CitySeeder::class);

    $cities = City::where('province_id', 6)->get();

    $response = $this->getJson($this->uri(6));

    $response->assertOk()
      ->assertExactJson(['data' => $this->formatCityData($cities)])
      ->assertJsonCount(6, 'data');
  }

  /** @test */
  public function returns_not_found_error_if_province_id_doenot_exist()
  {
    $response = $this->getJson($this->uri(35));

    $response->assertNotFound()
      ->assertJsonStructure(['message']);
  }
}
