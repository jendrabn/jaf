<?php

namespace Tests\Feature\Api;

use App\Models\ProductCategory;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryGetTest extends TestCase
{
  use RefreshDatabase;

  private string $uri = '/api/categories';

  /** @test */
  public function can_get_all_categories()
  {
    $this->seed(ProductCategorySeeder::class);

    $categories = ProductCategory::all();

    $response = $this->getJson($this->uri);

    $response->assertOk()
      ->assertExactJson(['data' => $this->formatCategoryData($categories)])
      ->assertJsonCount(3, 'data');
  }
}
