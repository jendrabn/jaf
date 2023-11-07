<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentBank;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    // \App\Models\User::factory(10)->create();

    // \App\Models\User::factory()->create([
    //     'name' => 'Test User',
    //     'email' => 'test@example.com',
    // ]);

    $this->call([
      RolesAndPermissionsSeeder::class,
      ProvinceSeeder::class,
      CitySeeder::class,
      ProductCategorySeeder::class,
      ProductBrandSeeder::class
    ]);

    User::create([
      'name' => 'Admin',
      'email' => 'admin@mail.com',
      'password' => 'password'
    ])->assignRole(User::ROLE_ADMIN);

    User::create([
      'name' => 'User',
      'email' => 'user@mail.com',
      'password' => 'password'
    ])->assignRole(User::ROLE_USER);

    Product::factory(20, ['is_publish' => fake()->boolean()])->hasImages(3)->create();
    User::factory(15)->afterCreating(fn (User $user) => $user->assignRole(User::ROLE_USER))->create();

    Order::factory(5, ['status' => Order::STATUS_PENDING_PAYMENT])
      ->has(OrderItem::factory(random_int(1, 3)), 'items')
      ->has(Invoice::factory(state: ['status' => Invoice::STATUS_UNPAID])
        ->has(Payment::factory(state: ['status' => Payment::STATUS_PENDING])))
      ->has(Shipping::factory(state: ['status' => Shipping::STATUS_PENDING]));

    Order::factory(5, ['status' => Order::STATUS_PENDING])
      ->has(OrderItem::factory(random_int(1, 3)), 'items')
      ->has(Invoice::factory(state: ['status' => Invoice::STATUS_UNPAID])
        ->has(Payment::factory(state: ['status' => Payment::STATUS_PENDING])
          ->has(PaymentBank::factory())))
      ->has(Shipping::factory(state: ['status' => Shipping::STATUS_PENDING]));

    Order::factory(5, ['status' => Order::STATUS_PROCESSING])
      ->has(OrderItem::factory(random_int(1, 3)), 'items')
      ->has(Invoice::factory(state: ['status' => Invoice::STATUS_PAID])
        ->has(Payment::factory(state: ['status' => Payment::STATUS_RELEASED])
          ->has(PaymentBank::factory())))
      ->has(Shipping::factory(state: ['status' => Shipping::STATUS_PENDING]));

    Order::factory(5, ['status' => Order::STATUS_ON_DELIVERY])
      ->has(OrderItem::factory(random_int(1, 3)), 'items')
      ->has(Invoice::factory(state: ['status' => Invoice::STATUS_PAID])
        ->has(Payment::factory(state: ['status' => Payment::STATUS_RELEASED])
          ->has(PaymentBank::factory())))
      ->has(Shipping::factory(state: ['status' => Shipping::STATUS_PROCESSING]));

    Order::factory(5, ['status' => Order::STATUS_COMPLETED])
      ->has(OrderItem::factory(random_int(1, 3)), 'items')
      ->has(Invoice::factory(state: ['status' => Invoice::STATUS_PAID])
        ->has(Payment::factory(state: ['status' => Payment::STATUS_RELEASED])
          ->has(PaymentBank::factory())))
      ->has(Shipping::factory(state: ['status' => Shipping::STATUS_SHIPPED]));

    Order::factory(5, ['status' => Order::STATUS_CANCELLED])
      ->has(OrderItem::factory(random_int(1, 3)), 'items')
      ->has(Invoice::factory(state: ['status' => Invoice::STATUS_UNPAID])
        ->has(Payment::factory(state: ['status' => Payment::STATUS_CANCELLED])
          ->has(PaymentBank::factory())))
      ->has(Shipping::factory(state: ['status' => Shipping::STATUS_PENDING]));
  }
}
