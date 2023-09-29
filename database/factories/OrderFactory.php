<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'user_id' => User::inRandomOrder()->first()->id,
      'total_price' => fake()->numberBetween(100000, 1000000),
      'shipping_cost' => fake()->numberBetween(5000, 50000),
      'notes' => fake()->text(),
      'cancel_reason' => null,
      'status' => fake()->randomElement([
        'pending_payment',
        'pending',
        'processing',
        'on_delivery',
        'completed',
        'cancelled'
      ]),
      'confirmed_at' => fake()->time(),
      'cancelled_at' => fake()->time(),
      'completed_at' => fake()->time(),
    ];
  }
}
