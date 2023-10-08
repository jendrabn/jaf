<?php
// tests/Feature/Api/OrderConfirmOrderDeliveredPutTest.php
namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\Shipping;
use App\Models\User;
use Database\Seeders\CitySeeder;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery\MockInterface;
use Tests\TestCase;

class OrderConfirmOrderDeliveredPutTest extends TestCase
{
  use RefreshDatabase;

  private User $user;

  protected function setUp(): void
  {
    parent::setUp();
    $this->user = $this->createUser();
  }

  /** @test */
  public function returns_unauthenticated_error_if_user_is_not_authenticated()
  {
    $response = $this->putJson(
      '/api/orders/1/confirm_order_delivered',
      headers: ['Authorization' => 'Bearer Invalid-Token']
    );

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function returns_not_found_error_if_order_id_doenot_exist()
  {
    $order = Order::factory()->for(User::factory())->create();

    $response1 = $this->attemptToConfirmOrderDelivered($order->id + 1);

    $response1->assertNotFound()
      ->assertJsonStructure(['message']);

    $response2 = $this->attemptToConfirmOrderDelivered($order->id);

    $response2->assertNotFound()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function cannot_confirm_payment_if_order_status_is_not_on_delivery()
  {
    $order = Order::factory()->for($this->user)
      ->create(['status' => Order::STATUS_PROCESSING]);

    $response = $this->attemptToConfirmOrderDelivered($order->id);

    $response->assertUnprocessable()
      ->assertJsonStructure(['message', 'errors' => ['*' => []]])
      ->assertJsonValidationErrorFor('order');
  }

  /** @test */
  public function can_confirm_order_delivered()
  {
    $this->seed([ProvinceSeeder::class, CitySeeder::class]);
    $order = Order::factory()
      ->has(Shipping::factory()->state(['status' => Shipping::STATUS_PROCESSING]))
      ->for($this->user)
      ->create(['status' => Order::STATUS_ON_DELIVERY]);

    $response = $this->attemptToConfirmOrderDelivered($order->id);

    $response->assertOk()->assertExactJson(['data' => true]);

    $this->assertTrue($order->fresh()->status === Order::STATUS_COMPLETED);
    $this->assertTrue($order->fresh()->shipping->status === Shipping::STATUS_SHIPPED);
  }

  private function attemptToConfirmOrderDelivered(int $id = 1)
  {
    $response = $this->putJson('/api/orders/' . $id . '/confirm_order_delivered', headers: $this->authBearerToken($this->user),);

    return $response;
  }
}
