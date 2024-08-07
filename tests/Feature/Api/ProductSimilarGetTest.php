<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use Database\Seeders\{ProductBrandSeeder, ProductCategorySeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ProductSimilarGetTest extends TestCase
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();
    $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);
  }

  #[Test]
  public function can_get_similar_products_by_product_id()
  {
    $this->createProduct(count: 3);
    $products = Product::factory(7)
      ->sequence(['name' => 'Bvlgari ' . fake()->sentence(2)])
      ->create();

    $response = $this->getJson('/api/products/' . $id = $products->first()->id . '/similars');

    $expectedProducts = $products->where('id', '!==', $id)->sortByDesc('id')->take(5);

    $response->assertExactJson(['data' => $this->formatProductData($expectedProducts)])
      ->assertJsonCount(5, 'data');
  }

  #[Test]
  public function returns_not_found_error_if_product_id_doenot_exist()
  {
    $product = $this->createProduct();

    $response = $this->getJson('/api/products/' . $product->id + 1 . '/similars');

    $response->assertNotFound()
      ->assertJsonStructure(['message']);
  }
}
