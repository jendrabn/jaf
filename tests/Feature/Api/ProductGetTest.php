<?php

// tests/Feature/Api/ProductGetTest.php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\TestCase;

class ProductGetTest extends TestCase
{
  /**
   * - Search by product name, category name & brand name (belum mendukung full-text search)
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
    // Cek pagination, gambar harus bisa diakses, status produk harus is_publish = true
    $this->createProduct(['is_publish' => false]); // id 1
    $product = $this->createProduct(imageCount: 2); // id 2
    $this->createProduct(count: 20); //id 3 - 23

    $response = $this->attemptToGetProduct(['page' => 2]);

    $response
      ->assertJsonPath('data.0', $this->expectedProduct($product))
      ->assertJsonCount(1, 'data')
      ->assertJsonPath('page', [
        'total' => 21,
        'per_page' => 20,
        'current_page' => 2,
        'last_page' => 2,
        'from' => 21,
        'to' => 21,
      ]);

    // $response = $this->get($response->json('data.0.image'));
    // $response->assertOk();
  }

  /** @test */
  public function can_get_products_by_search()
  {
    $products = Product::factory()
      ->count(3)
      ->sequence(
        [
          'name' => 'Parfum Aroma Bunga Mawar',
          'product_category_id' => $this->createCategory()->id,
          'product_brand_id' => $this->createBrand()->id,
        ],
        [
          'name' => 'Parfum Aroma Jeruk',
          'product_category_id' => $this->createCategory(['name' => '100ml'])->id,
          'product_brand_id' => $this->createBrand()->id,
        ],
        [
          'name' => 'Parfum Wangi Bunga Jasmine',
          'product_category_id' => $this->createCategory()->id,
          'product_brand_id' => $this->createBrand(['name' => 'Roses Musk'])->id,
        ],
      )
      ->create();

    // Search by product name
    $response = $this->attemptToGetProduct(['search' => 'Bunga Mawar']);
    $response->assertJsonPath('data.0', $this->expectedProduct($products[0]))
      ->assertJsonCount(1, 'data');

    // Search by category name
    $response = $this->attemptToGetProduct(['search' => '100ml']);
    $response->assertJsonPath('data.0', $this->expectedProduct($products[1]))
      ->assertJsonCount(1, 'data');

    // Search by brand name
    $response = $this->attemptToGetProduct(['search' => 'Musk']);
    $response->assertJsonPath('data.0', $this->expectedProduct($products[2]))
      ->assertJsonCount(1, 'data');
  }

  /** @test */
  public function can_sort_products_by_newest()
  {
    $products = $this->createProduct(count: 3);

    $response =  $this->attemptToGetProduct(['sort_by' => 'newest']);
    $response->assertJsonPath('data', $this->expectedProduct($products->sortByDesc('id')))
      ->assertJsonCount(3, 'data');
  }

  /** @test */
  public function can_sort_products_by_oldest()
  {
    $products = $this->createProduct(count: 3);

    $response =  $this->attemptToGetProduct(['sort_by' => 'oldest']);
    $response->assertJsonPath('data', $this->expectedProduct($products->sortBy('id')))
      ->assertJsonCount(3, 'data');
  }

  /** @test */
  public function can_sort_products_by_sales()
  {
    $product1 = $this->createProductWithOrderItem(status: Order::STATUS_PENDING_PAYMENT);
    $product2 = $this->createProductWithOrderItem(status: Order::STATUS_PENDING);
    $product3 = $this->createProductWithOrderItem(status: Order::STATUS_PROCESSING);
    $product4 = $this->createProductWithOrderItem(status: Order::STATUS_ON_DELIVERY);
    $product5 = $this->createProductWithOrderItem(status: Order::STATUS_CANCELLED);
    $product6 = $this->createProductWithOrderItem([
      ['quantity' => 3],
      ['quantity' => 2],
    ], Order::STATUS_COMPLETED);
    $product7 = $this->createProductWithOrderItem([
      ['quantity' => 2],
      ['quantity' => 1],
    ], Order::STATUS_COMPLETED);

    $response = $this->attemptToGetProduct(['sort_by' => 'sales']);
    $response
      ->assertJsonPath('data.0.id', $product6->id)
      ->assertJsonPath('data.0.sold_count', 5)
      ->assertJsonPath('data.1.id', $product7->id)
      ->assertJsonPath('data.1.sold_count', 3)
      ->assertJsonCount(7, 'data');

    $this->assertTrue(
      count(Arr::where($response->json('data'), fn ($product) => $product['sold_count'] > 0))
        === 2
    );

    $this->assertTrue(
      count(Arr::where($response->json('data'), fn ($product) => $product['sold_count'] === 0))
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
      ->create();

    $response = $this->attemptToGetProduct(['sort_by' => 'expensive']);
    $response->assertJsonPath('data', $this->expectedProduct($products->sortByDesc('price')))
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
      ->create();

    $response = $this->attemptToGetProduct(['sort_by' => 'cheapest']);
    $response->assertJsonPath('data', $this->expectedProduct($products->sortBy('price')))
      ->assertJsonCount(3, 'data');
  }

  /** @test */
  public function can_filter_products_by_category_id()
  {
    $categories = ProductCategory::all();
    $products = Product::factory()
      ->count(3)
      ->sequence(
        ['product_category_id' => $categories[0]->id],
        ['product_category_id' => $categories[0]->id],
        ['product_category_id' => $categories[1]->id],
      )
      ->create();

    $response = $this->attemptToGetProduct(['category_id' => $categories[0]->id]);
    $response->assertJsonPath('data', $this->expectedProduct(
      $products->where('product_category_id', $categories[0]->id)
        ->sortByDesc('id')
    ))
      ->assertJsonCount(2, 'data');
  }

  /** @test */
  public function can_filter_products_by_brand_id()
  {
    $brands = ProductBrand::all();
    $products = Product::factory()
      ->count(3)
      ->sequence(
        ['product_brand_id' => $brands[0]->id],
        ['product_brand_id' => $brands[0]->id],
        ['product_brand_id' => $brands[1]->id],
      )
      ->create();

    $response = $this->attemptToGetProduct(['brand_id' => $brands[0]->id]);
    $response->assertJsonPath('data', $this->expectedProduct(
      $products->where('product_brand_id', $brands[0]->id)
        ->sortByDesc('id')
    ))
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

    $response = $this->attemptToGetProduct(['sex' => 1]);
    $response->assertJsonPath('data', $this->expectedProduct(
      $products->where('sex', 1)
        ->sortByDesc('id')
    ))
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

    $response = $this->attemptToGetProduct([
      'price_min' => $min = 1000,
      'price_max' => $max = 5000
    ]);
    $response->assertJsonPath('data', $this->expectedProduct(
      $products->whereBetween('price', [$min, $max])
        ->sortByDesc('id')
    ))
      ->assertJsonCount(3, 'data');
  }

  public function createProductWithOrderItem(?array $quantity = [['quantity' => 1]], ?string $status = Order::STATUS_COMPLETED)
  {
    $this->createUser();

    return Product::factory()
      ->has(
        OrderItem::factory()
          ->count(count($quantity))
          ->state(['order_id' => Order::factory()->create(['status' => $status])->id])
          ->sequence(...$quantity)
      )
      ->create();
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
