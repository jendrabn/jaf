<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provinces = json_decode(file_get_contents(database_path('data/provinces.json')), true);

        Province::insert(
            Arr::map($provinces, fn($province) => Arr::only($province, ['id', 'name']))
        );
    }
}
