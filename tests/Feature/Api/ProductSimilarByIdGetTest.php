<?php
// tests/Feature/Api/ProductSimilarByIdGetTest.php
namespace Tests\Feature\Api;

use App\Models\Product;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductSimilarByIdGetTest extends TestCase
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();
    $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);
  }

  /** @test */
  public function can_get_similar_product_by_product_id()
  {
    $this->createProduct(count: 3);
    $products = Product::factory()
      ->count(7)
      ->sequence(['name' => 'Bvlgari ' . fake()->sentence(2)])
      ->create();

    $response = $this->getJson('/api/products/' . $products[0]->id . '/similars');

    $response->assertExactJson([
      'data' => $this->formatProductData(
        $products->where('id', '!==', $products[0]->id)->sortByDesc('id')->take(5)
      )
    ])
      ->assertJsonCount(5, 'data');
  }

  /** @test */
  public function returns_not_found_error_if_product_id_doenot_exist()
  {
    $product = $this->createProduct();

    $response = $this->getJson('/api/products/' . $product->id + 1 . '/similars');

    $response->assertNotFound()
      ->assertJsonStructure(['message']);
  }
}
