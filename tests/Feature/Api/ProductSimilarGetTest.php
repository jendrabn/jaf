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

  protected function setUp(): void
  {
    parent::setUp();
    $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);
  }

  private static function URI(int $productId = 1): string
  {
    return "/api/products/{$productId}/similars";
  }

  /** @test */
  public function can_get_similar_products_by_product_id()
  {
    $this->createProduct(count: 3);
    $products = Product::factory(7)
      ->sequence(['name' => 'Bvlgari ' . fake()->sentence(2)])
      ->create();

    $response = $this->getJson(self::URI($id = $products->first()->id));

    $response->assertExactJson([
      'data' => $this->formatProductData(
        $products->where('id', '!==', $id)->sortByDesc('id')->take(5)
      )
    ])->assertJsonCount(5, 'data');
  }

  /** @test */
  public function returns_not_found_error_if_product_id_doenot_exist()
  {
    $product = $this->createProduct();

    $response = $this->getJson(self::URI($product->id + 1));

    $response->assertNotFound()
      ->assertJsonStructure(['message']);
  }
}
