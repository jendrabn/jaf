<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use Database\Seeders\{ProductBrandSeeder, ProductCategorySeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductSimilarGetTest extends TestCase
{
  use RefreshDatabase;

  private string $uri = '/api/products';

  protected function setUp(): void
  {
    parent::setUp();
    $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);
  }

  private function uri(int $productId = 1): string
  {
    return '/api/products/' . $productId . '/similars';
  }

  /** @test */
  public function can_get_similar_products_by_product_id()
  {
    $this->createProduct(count: 3);
    $products = Product::factory(count: 7)
      ->sequence(['name' => 'Bvlgari ' . fake()->sentence(2)])
      ->create();
    $productId = $products->first()->id;

    $response = $this->getJson($this->uri($productId));

    $response->assertExactJson([
      'data' => $this->formatProductData($products->where('id', '!==', $productId)
        ->sortByDesc('id')
        ->take(5))
    ])->assertJsonCount(5, 'data');
  }

  /** @test */
  public function returns_not_found_error_if_product_id_doenot_exist()
  {
    $product = $this->createProduct();

    $response = $this->getJson($this->uri($product->id + 1));

    $response->assertNotFound()
      ->assertJsonStructure(['message']);
  }
}
