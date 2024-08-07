<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentBank;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            RolesAndPermissionsSeeder::class,
            ProvinceSeeder::class,
            CitySeeder::class,
            ProductCategorySeeder::class,
            ProductBrandSeeder::class,
            BankSeeder::class
        ]);

        User::create([
            'name' => 'Admin',
            'email' => 'admin@mail.com',
            'password' => 'Secret123',
        ])->assignRole(User::ROLE_ADMIN);

        User::create([
            'name' => 'User',
            'email' => 'user@mail.com',
            'password' => 'Secret123',
        ])->assignRole(User::ROLE_USER);

        Product::factory(50)->hasImages()->create();

        for ($i = 0; $i < 100; $i++) {
            Order::factory()
                ->has(OrderItem::factory(random_int(1, 5)), 'items')
                ->has(Invoice::factory()->has(Payment::factory()->has(PaymentBank::factory(), 'bank')))
                ->has(Shipping::factory())
                ->for(User::factory()->afterCreating(fn ($user) => $user->assignRole(User::ROLE_USER))->create())
                ->create();
        }
    }
}
