<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->bigInteger('total_price');
            $table->bigInteger('shipping_cost');
            $table->string('notes', 200)->nullable();
            $table->string('cancel_reason', 200)->nullable();
            $table->enum('status', [
                'pending_payment',
                'pending',
                'processing',
                'on_delivery',
                'completed',
                'cancelled'
            ]);
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        if (env('DB_CONNECTION') === 'mysql') {
            DB::update('ALTER TABLE orders AUTO_INCREMENT = 1000000');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
