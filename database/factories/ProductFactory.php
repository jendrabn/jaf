<?php

namespace Database\Factories;

use App\Models\ProductBrand;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'product_category_id' => ProductCategory::inRandomOrder()->first()->id,
      'product_brand_id' => ProductBrand::inRandomOrder()->first()->id,
      'name' => fake()->sentence(),
      'slug' => fake()->unique()->slug(),
      'weight' => fake()->numberBetween(1000, 5000),
      'price' => fake()->numberBetween(50000, 1000000),
      'stock' => fake()->numberBetween(100, 1000),
      'description' => fake()->paragraph(),
      'is_publish' => true,
      'sex' => fake()->randomElement([1, 2, 3]),
    ];
  }
}
