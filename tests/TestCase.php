<?php

namespace Tests;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\UploadedFile;
use JMac\Testing\Traits\AdditionalAssertions;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
  use CreatesApplication, AdditionalAssertions;

  protected function createUser(?array $data = [], int $count = 1): User|Collection
  {
    $users = User::factory()->count($count)->create($data);

    return $count > 1 ? $users : $users->first();
  }

  protected function authBearerToken(User $user, bool $isHeader = true): array|string
  {
    $token = $user->createToken('auth_token')->plainTextToken;

    return $isHeader ? ['Authorization' => 'Bearer ' . $token] : $token;
  }

  protected function createProduct(?array $data = [], int $count = 1, int $imageCount = 0)
  {
    $products = Product::factory()->count($count)->create($data);

    if ($imageCount > 0) {
      $products->each(function ($product) use ($imageCount) {
        foreach (range(1, $imageCount) as $num) {
          $image = UploadedFile::fake()->image(Str::random(10) . '.jpg');

          $product->addMedia($image)->toMediaCollection('images');
        }
      });
    }

    return $count > 1 ? $products : $products->first();
  }

  protected function createCategory(?array $data = [], int $count = 1): ProductCategory|Collection
  {
    $categories = ProductCategory::factory()->count($count)->create($data);

    return $count > 1 ? $categories : $categories->first();
  }

  protected function createBrand(?array $data = [], int $count = 1): ProductBrand|Collection
  {
    $brands =  ProductBrand::factory()->count($count)->create($data);

    return $count > 1 ? $brands : $brands->first();
  }

  protected function expectedProduct(Product|Collection $product): array
  {
    if ($product instanceof Collection) {
      return $product->map(fn ($product) => $this->expectedProduct($product))
        ->values()
        ->toArray();
    }

    return [
      'id' => $product->id,
      'name' => $product->name,
      'slug' => $product->slug,
      'image' => $product->image,
      'category' => $product->category->only('id', 'name', 'slug'),
      'brand' => $product->brand->only('id', 'name', 'slug'),
      'sex' => $product->sex,
      'price' => $product->price,
      'stock' => $product->stock,
      'weight' => $product->weight,
      'sold_count' => $product->sold_count ?? 0,
      'is_wishlist' => $product->is_wishlist,
    ];
  }


  protected function createProductWithSales(?array $quantities = [1], ?string $status = Order::STATUS_COMPLETED): Product
  {
    $sequence = [];
    foreach ($quantities as $quantity) {
      $sequence[] = [
        'quantity' => $quantity,
        'order_id' => Order::factory()->for(User::factory()->create())
          ->create(compact('status'))->id
      ];
    }

    return Product::factory()
      ->has(OrderItem::factory(count($sequence))->sequence(...$sequence))
      ->create();
  }

  protected function addImageToProduct(Collection|Product $products, ?int $count = 1)
  {
    if ($products instanceof Collection) {
      return $products->each(
        fn ($product) => $this->addImageToProduct($product)
      );
    }

    for ($i = 0; $i < $count; $i++) {
      $file = fake()->image(storage_path('app/public'), 50, 50);

      $products->addMedia($file)->toMediaCollection('images');
    }

    return $products;
  }
}
