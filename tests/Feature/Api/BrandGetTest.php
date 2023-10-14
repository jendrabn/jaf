<?php

namespace Tests\Feature\Api;

use App\Models\ProductBrand;
use Database\Seeders\ProductBrandSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BrandGetTest extends TestCase
{
  use RefreshDatabase;

  private string $uri = '/api/brands';

  /** @test */
  public function can_get_all_brands()
  {
    $this->seed(ProductBrandSeeder::class);

    $brands = ProductBrand::all();

    $response = $this->getJson($this->uri);

    $response->assertOk()
      ->assertExactJson(['data' => $this->formatBrandData($brands)])
      ->assertJsonCount(3, 'data');
  }
}
