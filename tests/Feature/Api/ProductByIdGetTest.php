<?php

namespace Tests\Feature\Api;

use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductByIdGetTest extends TestCase
{
  use RefreshDatabase;

  private string $uri = '/api/products';

  protected function setUp(): void
  {
    parent::setUp();
    $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);
  }

  /** @test */
  public function can_get_product_by_id()
  {
    $product = $this->createProductWithSales([2, 3]);
    $this->addImageToProduct($product, 3);

    $response = $this->getJson($this->uri . '/' . $product->id);

    $response->assertOk()
      ->assertJsonStructure([
        "data" => [
          "id",
          "name",
          "slug",
          "images",
          "category",
          "description",
          "brand",
          "sex",
          "price",
          "stock",
          "weight",
          "sold_count",
          "is_wishlist",
        ]
      ])
      ->assertJsonPath('data.name', $product->name)
      ->assertJsonPath('data.images', $product->images)
      ->assertJsonPath('data.sold_count', 5)
      ->assertJsonCount(3, 'data.images');
  }

  /** @test */
  public function returns_not_found_error_if_product_id_doenot_exist()
  {
    $product = $this->createProduct();

    $response = $this->getJson($this->uri . '/' . $product->id + 1);
    $response->assertNotFound()
      ->assertJsonStructure(['message']);
  }
}
