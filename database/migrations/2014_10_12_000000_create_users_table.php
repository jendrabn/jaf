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
    Schema::create('users', function (Blueprint $table) {
      $table->id();
      $table->string('name', 30);
      $table->string('email')->unique();
      $table->timestamp('email_verified_at')->nullable();
      $table->string('password', 30);
      $table->string('phone', 15)->nullable();
      $table->enum('sex', [1, 2])->comment('1:male, 2:female');
      $table->date('birth_date');
      $table->rememberToken();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('users');
  }
};
