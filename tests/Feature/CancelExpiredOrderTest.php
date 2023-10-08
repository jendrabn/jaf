<?php
// tests/Feature/CancelExpiredOrderTest.php
namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CancelExpiredOrderTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function can_cancel_expired_order()
  {
    $user = $this->createUser();
    $order = Order::factory()
      ->for($user)
      ->afterCreating(
        fn (Order $order) => Invoice::factory()
          ->create(['due_date' => $order->created_at->addDays(1)])
      )
      ->create(['status' => Order::STATUS_PENDING_PAYMENT]);

    $this->travel(25)->hours();

    $this->artisan('app:cancel-expired-order');

    $this->assertTrue($order->fresh()->status === Order::STATUS_CANCELLED);
  }
}
