<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\ApiTestCase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CancelExpiredOrderTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_cancel_expired_order()
    {
        $order = Order::factory()
            ->for($this->createUser())
            ->afterCreating(
                fn(Order $order) => Invoice::factory()->create(['due_date' => $order->created_at->addDays(1)])
            )
            ->create(['status' => Order::STATUS_PENDING_PAYMENT]);

        $this->travel(25)->hours();

        $this->artisan('app:cancel-expired-order');

        $this->assertTrue($order->fresh()->status === Order::STATUS_CANCELLED);
    }
}
