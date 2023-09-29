<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('products', function (Blueprint $table) {
      $table->id();
      $table->foreignId('product_category_id')->constrained('product_categories');
      $table->foreignId('product_brand_id')->nullable()->constrained('product_brands');
      $table->string('name', 200);
      $table->string('slug')->unique();
      $table->integer('weight')->comment('gram');
      $table->integer('price');
      $table->integer('stock')->default(1);
      $table->text('description')->nullable();
      $table->boolean('is_publish')->default(true);
      $table->enum('sex', [1, 2, 3])->comment('1:male, 2:female, 3:unisex');
      // $table->string('images');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('products');
  }
};
