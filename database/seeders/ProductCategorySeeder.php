<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    ProductCategory::create(['name' => 'Parfum', 'slug' => 'parfum']);
    ProductCategory::create(['name' => 'Parfum Laundry', 'slug' => 'parfum-laundry']);
    ProductCategory::create(['name' => 'Botol Parfum', 'slug' => 'botol-parfum']);
  }
}
