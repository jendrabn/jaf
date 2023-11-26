<?php

namespace Tests\Feature\Api;

use App\Models\Banner;
use Database\Seeders\{ProductBrandSeeder, ProductCategorySeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageGetTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function can_get_banners_and_latest_products()
  {
    $this->seed([ProductCategorySeeder::class, ProductBrandSeeder::class]);

    $this->createProduct(['is_publish' => false]);
    $banners = Banner::factory(12)->hasImage()->create();
    $products = $this->createProduct(count: 12);

    $expectedBanners = $banners->sortBy('id')->take(10);
    $expectedProducts = $products->sortByDesc('id')->take(10);


    $response = $this->getJson('/api/home_page');

    $response
      ->assertOk()
      ->assertExactJson([
        'data' => [
          'banners' =>
          $expectedBanners->map(fn ($banner) => [
            'id' => $banner->id,
            'image' => $banner->image ? $banner->image->getUrl() : null,
            'image_alt' => $banner->image_alt,
            'url' => $banner->url
          ])->toArray(),
          'products' => $this->formatProductData($expectedProducts)
        ]
      ]);

    $this->assertStringStartsWith('http', $response['data']['banners'][0]['image']);
  }
}
