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

  /** @test */
  public function can_get_all_categories()
  {
    $this->seed(ProductCategorySeeder::class);
    $categories = ProductCategory::all()
      ->map(fn ($category) => $category->only(['id', 'name', 'slug',]))
      ->toArray();

    $response = $this->getJson('/api/categories');

    $response->assertOk()
      ->assertJsonStructure(['data' => ['*' => ['id', 'name', 'slug',]]])
      ->assertExactJson(['data' => $categories])
      ->assertJsonCount(3, 'data');
  }
}
