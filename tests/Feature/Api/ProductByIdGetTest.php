<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductByIdGetTest extends TestCase
{
  use RefreshDatabase;

  private string $uri = '/api/products/';

  protected function setUp(): void
  {
    parent::setUp();
    $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);
  }

  /** @test */
  public function can_get_product_by_id()
  {
    $product = Product::factory()
      ->has(
        OrderItem::factory()
          ->count(2)
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

    $response = $this->getJson($this->uri . $product->id);

    $response->assertOk()
      ->assertExactJson([
        "data" => [
          "id" => $product->id,
          "name" => $product->name,
          "slug" => $product->slug,
          "images" => $product->images,
          "category" => $this->formatCategoryData($product->category),
          "description" => $product->description,
          "brand" => $this->formatBrandData($product->brand),
          "sex" => $product->sex,
          "price" => $product->price,
          "stock" => $product->stock,
          "weight" => $product->weight,
          "sold_count" => 5,
          "is_wishlist" => false,
        ]
      ])
      ->assertJsonCount(3, 'data.images');
  }

  /** @test */
  public function returns_not_found_error_if_product_id_doenot_exist()
  {
    $product = $this->createProduct();

    $response = $this->getJson($this->uri . $product->id + 1);

    $response->assertNotFound()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function returns_not_found_error_if_product_is_unpublished()
  {
    $product = $this->createProduct(['is_publish' => false]);

    $response = $this->getJson($this->uri . $product->id);

    $response->assertNotFound()
      ->assertJsonStructure(['message']);
  }
}
