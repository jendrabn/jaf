<?php

namespace Database\Seeders;

use App\Models\ProductBrand;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductBrandSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    ProductBrand::create(['name' => 'Bvlgari', 'slug' => 'Bvlgari',]);
    ProductBrand::create(['name' => 'Dior', 'slug' => 'Dior',]);
    ProductBrand::create(['name' => 'Hugo Boss', 'slug' => 'Hugo Boss',]);
  }
}
