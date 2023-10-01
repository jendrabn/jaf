<?php

// tests/Feature/Api/ProductGetTest.php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\Product;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\TestCase;

class ProductGetTest extends TestCase
{
  /**
   * - Search by product name, category & brand (bukan full-text search)
   * - Sorting by newest, oldest, sales, expensive, cheapest
   * - Filter by category id, brand id, sex, min price & max price
   * - Default orderBy jika tidak ada param sort_by atau value sort_by tidak valid
   *   adalah sort by ID desc (newest)
   */

  use RefreshDatabase;

  private string $uri = '/api/products';

  protected function setUp(): void
  {
    parent::setUp();
    $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);
  }

  /** @test */
  public function can_get_all_products()
  {
    $products = $this->createProduct(count: 21);
    $firstProduct = $this->addImageToProduct($products->first());

    $response = $this->attemptToGetProduct(['page' => 2]);

    $response
      ->assertJsonPath('data.0', $this->formatProductData($firstProduct))
      ->assertJsonCount(1, 'data')
      ->assertJsonPath('page', [
        'total' => 21,
        'per_page' => 20,
        'current_page' => 2,
        'last_page' => 2,
        'from' => 21,
        'to' => 21,
      ]);
  }

  /** @test */
  public function can_get_products_by_search()
  {
    $products = Product::factory()
      ->count(3)
      ->sequence(
        [
          'name' => 'Parfum Aroma Bunga Mawar',
          'product_category_id' => 1,
          'product_brand_id' => 1,
        ],
        [
          'name' => 'Parfum Aroma Jeruk',
          'product_category_id' => $this->createCategory(['name' => '100ml'])->id,
          'product_brand_id' => 2,
        ],
        [
          'name' => 'Parfum Wangi Bunga Jasmine',
          'product_category_id' => 2,
          'product_brand_id' => $this->createBrand(['name' => 'Roses Musk'])->id,
        ],
      )
      ->create();

    // By name
    $response = $this->attemptToGetProduct(['search' => 'Bunga Mawar']);

    $response->assertJsonPath('data.0', $this->formatProductData($products[0]))
      ->assertJsonCount(1, 'data');

    // By category
    $response = $this->attemptToGetProduct(['search' => '100ml']);

    $response->assertJsonPath('data.0', $this->formatProductData($products[1]))
      ->assertJsonCount(1, 'data');

    // By brand
    $response = $this->attemptToGetProduct(['search' => 'Musk']);

    $response->assertJsonPath('data.0', $this->formatProductData($products[2]))
      ->assertJsonCount(1, 'data');
  }

  /** @test */
  public function can_sort_products_by_newest()
  {
    $products = $this->createProduct(count: 3)->sortByDesc('id');

    $response =  $this->attemptToGetProduct(['sort_by' => 'newest']);

    $response->assertJsonPath('data', $this->formatProductData($products))
      ->assertJsonCount(3, 'data');
  }

  /** @test */
  public function can_sort_products_by_oldest()
  {
    $products = $this->createProduct(count: 3)->sortBy('id');

    $response =  $this->attemptToGetProduct(['sort_by' => 'oldest']);

    $response->assertJsonPath('data', $this->formatProductData($products))
      ->assertJsonCount(3, 'data');
  }

  /** @test */
  public function can_sort_products_by_sales()
  {
    $product1 = $this->createProductWithSales(status: Order::STATUS_PENDING_PAYMENT);
    $product2 = $this->createProductWithSales(status: Order::STATUS_PENDING);
    $product3 = $this->createProductWithSales(status: Order::STATUS_PROCESSING);
    $product4 = $this->createProductWithSales(status: Order::STATUS_ON_DELIVERY);
    $product5 = $this->createProductWithSales(status: Order::STATUS_CANCELLED);
    $product6 = $this->createProductWithSales(quantities: [3, 2], status: Order::STATUS_COMPLETED);
    $product7 = $this->createProductWithSales(quantities: [2, 1], status: Order::STATUS_COMPLETED);

    $response = $this->attemptToGetProduct(['sort_by' => 'sales']);

    $response
      ->assertJsonPath('data.0.id', $product6->id)
      ->assertJsonPath('data.0.sold_count', 5)
      ->assertJsonPath('data.1.id', $product7->id)
      ->assertJsonPath('data.1.sold_count', 3)
      ->assertJsonCount(7, 'data')
      ->json();

    $this->assertTrue(
      count(Arr::where($response['data'], fn ($product) => $product['sold_count'] > 0))
        === 2
    );

    $this->assertTrue(
      count(Arr::where($response['data'], fn ($product) => $product['sold_count'] === 0))
        === 5
    );
  }

  /** @test */
  public function can_sort_products_by_expensive()
  {
    $products = Product::factory()
      ->count(3)
      ->sequence(
        ['price' => 1000],
        ['price' => 3000],
        ['price' => 2000],
      )
      ->create()
      ->sortByDesc('price');

    $response = $this->attemptToGetProduct(['sort_by' => 'expensive']);

    $response->assertJsonPath('data', $this->formatProductData($products))
      ->assertJsonCount(3, 'data');
  }

  /** @test */
  public function can_sort_products_by_cheapest()
  {
    $products = Product::factory()
      ->count(3)
      ->sequence(
        ['price' => 3000],
        ['price' => 1000],
        ['price' => 2000]
      )
      ->create()
      ->sortBy('price');

    $response = $this->attemptToGetProduct(['sort_by' => 'cheapest']);

    $response->assertJsonPath('data', $this->formatProductData($products))
      ->assertJsonCount(3, 'data');
  }

  /** @test */
  public function can_filter_products_by_category_id()
  {
    $products = Product::factory()
      ->count(3)
      ->sequence(
        ['product_category_id' => 1],
        ['product_category_id' => 1],
        ['product_category_id' => 2],
      )
      ->create();
    $products = $products->where('product_category_id', 1)
      ->sortByDesc('id');

    $response = $this->attemptToGetProduct(['category_id' => 1]);

    $response->assertJsonPath('data', $this->formatProductData($products))
      ->assertJsonCount(2, 'data');
  }

  /** @test */
  public function can_filter_products_by_brand_id()
  {
    $products = Product::factory()
      ->count(3)
      ->sequence(
        ['product_brand_id' => 1],
        ['product_brand_id' => 1],
        ['product_brand_id' => 2],
      )
      ->create();
    $products = $products->where('product_brand_id', 1)
      ->sortByDesc('id');

    $response = $this->attemptToGetProduct(['brand_id' => 1]);

    $response->assertJsonPath('data', $this->formatProductData($products))
      ->assertJsonCount(2, 'data');
  }

  /** @test */
  public function can_filter_products_by_sex()
  {
    $products = Product::factory()
      ->count(3)
      ->sequence(
        ['sex' => 1],
        ['sex' => 1],
        ['sex' => 2],
      )
      ->create();
    $products = $products->where('sex', 1)
      ->sortByDesc('id');

    $response = $this->attemptToGetProduct(['sex' => 1]);

    $response->assertJsonPath('data', $this->formatProductData($products))
      ->assertJsonCount(2, 'data');
  }

  /** @test */
  public function can_filter_products_by_price_min_and_price_max()
  {
    $products = Product::factory()
      ->count(5)
      ->sequence(
        ['price' => 500],
        ['price' => 3000],
        ['price' => 1000],
        ['price' => 5000],
        ['price' => 7000],
      )
      ->create();
    $products = $products->whereBetween('price', [$min = 1000, $max = 5000])
      ->sortByDesc('id');

    $response = $this->attemptToGetProduct(['price_min' => $min, 'price_max' => $max]);

    $response->assertJsonPath('data', $this->formatProductData($products))
      ->assertJsonCount(3, 'data');
  }

  public function attemptToGetProduct(?array $params = [])
  {
    $response = $this->getJson($this->uri . '?' . http_build_query($params));

    $response->assertOk()
      ->assertJsonStructure([
        'data' => [
          '*' => [
            'id',
            'name',
            'slug',
            'image',
            'category',
            'brand',
            'sex',
            'price',
            'stock',
            'weight',
            'sold_count',
            'is_wishlist',
          ],
        ],
        'page' => [
          'total',
          'per_page',
          'current_page',
          'last_page',
          'from',
          'to',
        ],
      ]);

    return $response;
  }
}
