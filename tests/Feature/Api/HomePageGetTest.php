<?php

namespace Tests\Feature\Api;

use App\Models\Banner;
use Database\Seeders\{ProductBrandSeeder, ProductCategorySeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HomePageGetTest extends TestCase
{
  use RefreshDatabase;

  private string $uri = '/api/home_page';

  /** @test */
  public function can_get_banners_and_latest_products_for_home_page()
  {
    $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);

    $this->createProduct(['is_publish' => false]);
    $products = $this->createProduct(count: 12);
    $banners = Banner::factory(count: 12)->hasImage()->create();

    $response = $this->getJson($this->uri);

    $response
      ->assertOk()
      ->assertExactJson([
        'data' => [
          'banners' => $banners->sortBy('id')->take(10)->map(fn ($banner) => [
            'id' => $banner->id,
            'image' => $banner->image,
            'image_alt' => $banner->image_alt,
            'url' => $banner->url,
          ])->toArray(),
          'products' => $this->formatProductData($products->sortByDesc('id')->take(10))
        ]
      ]);

    $this->assertStringStartsWith('http', $response['data']['banners'][0]['image']);
  }
}
