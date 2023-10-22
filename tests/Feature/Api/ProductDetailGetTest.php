<?php

namespace Tests\Feature\Api;

use App\Models\{Order, OrderItem, Product};
use Database\Seeders\{ProductBrandSeeder, ProductCategorySeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDetailGetTest extends TestCase
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();
    $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);
  }

  private static function URI(int $productId = 1): string
  {
    return "/api/products/{$productId}";
  }

  /** @test */
  public function can_get_product_by_id()
  {
    $product = Product::factory()
      ->has(
        OrderItem::factory(2)
          ->sequence(
            [
              'order_id' => $this->createOrder(['status' => Order::STATUS_COMPLETED])->id,
              'quantity' => 2,
            ],
            [
              'order_id' => $this->createOrder(['status' => Order::STATUS_COMPLETED])->id,
              'quantity' => 3
            ]
          )
      )
      ->hasImages(3)
      ->create();

    $response = $this->getJson(self::URI($product->id));

    $response->assertOk()
      ->assertExactJson([
        'data' => [
          'id' => $product->id,
          'name' => $product->name,
          'slug' => $product->slug,
          'images' => $product->images,
          'category' => $this->formatCategoryData($product->category),
          'description' => $product->description,
          'brand' => $this->formatBrandData($product->brand),
          'sex' => $product->sex,
          'price' => $product->price,
          'stock' => $product->stock,
          'weight' => $product->weight,
          'sold_count' => 5,
          'is_wishlist' => false,
        ]
      ])
      ->assertJsonCount(3, 'data.images');
  }

  /** @test */
  public function returns_not_found_error_if_product_id_doenot_exist()
  {
    $product = $this->createProduct();

    $response = $this->getJson(self::URI($product->id + 1));

    $response->assertNotFound()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function returns_not_found_error_if_product_is_not_published()
  {
    $product = $this->createProduct(['is_publish' => false]);

    $response = $this->getJson(self::URI($product->id));

    $response->assertNotFound()
      ->assertJsonStructure(['message']);
  }
}
