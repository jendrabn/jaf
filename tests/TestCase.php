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
use JMac\Testing\Traits\AdditionalAssertions;

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

  protected function createProduct(?array $data = [], int $count = 1): Product|Collection
  {
    $products = Product::factory()->count($count)->create($data);

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

  protected function formatCategoryData(ProductCategory|Collection $data): array
  {
    if ($data instanceof Collection) {
      return $data->map(
        fn ($data) => $this->formatCategoryData($data)
      )->values()->toArray();
    }

    return [
      'id' => $data['id'],
      'name' => $data['name'],
      'slug' => $data['slug'],
    ];
  }

  protected function formatBrandData(ProductBrand|Collection $data): array
  {
    if ($data instanceof Collection) {
      return $data->map(
        fn ($data) => $this->formatBrandData($data)
      )->values()->toArray();
    }

    return [
      'id' => $data['id'],
      'name' => $data['name'],
      'slug' => $data['slug'],
    ];
  }

  protected function formatProductData(Product|Collection $data): array
  {
    if ($data instanceof Collection) {
      return $data->map(
        fn ($data) => $this->formatProductData($data)
      )->values()->toArray();
    }

    return [
      'id' => $data['id'],
      'name' => $data['name'],
      'slug' => $data['slug'],
      'image' => $data['image'],
      'category' => $this->formatCategoryData($data['category']),
      'brand' => $this->formatBrandData($data['brand']),
      'sex' => $data['sex'],
      'price' => $data['price'],
      'stock' => $data['stock'],
      'weight' => $data['weight'],
      'sold_count' => $data['sold_count'] ?? 0,
      'is_wishlist' => $data['is_wishlist'] ?? false,
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
