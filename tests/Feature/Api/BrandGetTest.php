<?php

namespace Tests\Feature\Api;

use App\Models\ProductBrand;
use Database\Seeders\ProductBrandSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class BrandGetTest extends TestCase
{
  use RefreshDatabase;

  #[Test]
  public function can_get_all_brands()
  {
    $this->seed(ProductBrandSeeder::class);

    $brands = ProductBrand::all();

    $response = $this->getJson('/api/brands');

    $response->assertOk()
      ->assertExactJson(['data' => $this->formatBrandData($brands)])
      ->assertJsonCount(3, 'data');
  }
}
