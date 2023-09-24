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

  /** @test */
  public function can_get_all_brands()
  {
    $this->seed(ProductBrandSeeder::class);
    $brands = ProductBrand::all()
      ->map(fn ($brand) => $brand->only(['id', 'name', 'slug',]))
      ->toArray();

    $response = $this->getJson('/api/brands');

    $response->assertOk()
      ->assertJsonStructure(['data' => ['*' => ['id', 'name', 'slug',]]])
      ->assertExactJson(['data' => $brands])
      ->assertJsonCount(3, 'data');
  }
}
