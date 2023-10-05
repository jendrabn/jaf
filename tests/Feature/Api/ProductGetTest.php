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
  use RefreshDatabase;

  /**
   *  Search by product name, category & brand (belum full-text search)
   *  Sorting by newest, oldest, sales, expensive, cheapest
   *  Filter by category_id, brand_id, sex, min_price & max_price
   *  Default orderBy adalah order by ID desc (newest)
   */

  private string $uri = '/api/products';

  protected function setUp(): void
  {
    parent::setUp();
    $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);
  }

  /** @test */
  public function can_get_all_products()
  {
    $products = Product::factory()->count(3)->hasImages()->create()->sortByDesc('id');
    $this->createProduct(['is_publish' => false]);

    $response = $this->attemptToGetProduct();

    $response->assertJsonPath('data', $this->formatProductData($products))
      ->assertJsonCount(3, 'data');

    $this->assertNotTrue($response['data'][0]['image'] == '');
  }

  /** @test */
  public function can_get_products_by_page()
  {
    $this->createProduct(count: 23);

    $response = $this->attemptToGetProduct(['page' => 2]);

    $response
      ->assertJsonPath('page', [
        'total' => 23,
        'per_page' => 20,
        'current_page' => 2,
        'last_page' => 2,
        'from' => 21,
        'to' => 23,
      ])
      ->assertJsonCount(3, 'data');
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

    // Search by product name
    $response = $this->attemptToGetProduct(['search' => 'Bunga Mawar']);

    $response->assertJsonPath('data.0', $this->formatProductData($products[0]))
      ->assertJsonCount(1, 'data');

    // Search by category
    $response = $this->attemptToGetProduct(['search' => '100ml']);

    $response->assertJsonPath('data.0', $this->formatProductData($products[1]))
      ->assertJsonCount(1, 'data');

    // Search by brand
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
    $product1 = $this->createProductWithSales(quantities: [3, 2]);
    $product2 = $this->createProductWithSales(quantities: [2, 1]);
    $this->createProductWithSales(status: Order::STATUS_PENDING);
    $this->createProductWithSales(status: Order::STATUS_PROCESSING);
    $this->createProductWithSales(status: Order::STATUS_ON_DELIVERY);

    $response = $this->attemptToGetProduct(['sort_by' => 'sales']);

    $response
      ->assertJsonPath('data.0.id', $product1->id)
      ->assertJsonPath('data.0.sold_count', 5)
      ->assertJsonPath('data.1.id', $product2->id)
      ->assertJsonPath('data.1.sold_count', 3)
      ->assertJsonCount(5, 'data')
      ->json();

    $this->assertCount(3, Arr::where(
      $response['data'],
      fn ($product) => $product['sold_count'] === 0
    ));
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
      ->create()
      ->where('product_category_id', 1)
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
      ->create()
      ->where('product_brand_id', 1)
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
      ->create()
      ->where('sex', 1)
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
      ->create()
      ->whereBetween('price', [$min = 1000, $max = 5000])
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
