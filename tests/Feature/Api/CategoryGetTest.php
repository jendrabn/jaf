<?php

namespace Tests\Feature\Api;

use App\Models\ProductCategory;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CategoryGetTest extends TestCase
{
    use RefreshDatabase;

    // #[Test]
    public function test_can_get_all_categories(): void
    {
        $this->seed(ProductCategorySeeder::class);

        $categories = ProductCategory::all();

        $response = $this->getJson('/api/categories');

        $response->assertOk()
            ->assertExactJson(['data' => $this->formatCategoryData($categories)])
            ->assertJsonCount(3, 'data');
    }
}
