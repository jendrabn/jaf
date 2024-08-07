<?php

namespace Tests\Feature\Api;

use App\Models\Province;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RegionProvinceGetTest extends TestCase
{
  use RefreshDatabase;

  #[Test]
  public function can_get_all_provinces()
  {
    $this->seed(ProvinceSeeder::class);

    $provinces = Province::all();

    $response = $this->getJson('/api/region/provinces');

    $response->assertOk()
      ->assertExactJson(['data' => $this->formatProvinceData($provinces)])
      ->assertJsonCount(34, 'data');
  }
}
