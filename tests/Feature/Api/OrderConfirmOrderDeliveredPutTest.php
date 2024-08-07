<?php

namespace Tests\Feature\Api;

use App\Models\{Order, Shipping, User};
use Database\Seeders\{CitySeeder, ProvinceSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class OrderConfirmOrderDeliveredPutTest extends TestCase
{
  use RefreshDatabase;

  private User $user;

  protected function setUp(): void
  {
    parent::setUp();
    $this->user = $this->createUser();
  }

  #[Test]
  public function unauthenticated_user_cannot_confirm_order_delivered()
  {
    $response = $this->putJson('/api/orders/1/confirm_order_delivered');

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

  #[Test]
  public function can_confirm_order_delivered()
  {
    $this->seed([ProvinceSeeder::class, CitySeeder::class]);

    $order = Order::factory()
      ->has(Shipping::factory(state: ['status' => Shipping::STATUS_PROCESSING]))
      ->for($this->user)
      ->create(['status' => Order::STATUS_ON_DELIVERY]);

    $response = $this->putJson('/api/orders/' . $order->id . '/confirm_order_delivered', headers: $this->authBearerToken($this->user));

    $response->assertOk()
      ->assertExactJson(['data' => true]);

    $this->assertTrue($order->fresh()->status === Order::STATUS_COMPLETED);
    $this->assertTrue($order->shipping->fresh()->status === Shipping::STATUS_SHIPPED);
  }


  #[Test]
  public function cannot_confirm_order_delivered_if_order_doenot_exist()
  {
    $order = Order::factory()->for(User::factory())->create();

    // Unauthorized order id
    $response1 = $this->putJson(
      '/api/orders/' . $order->id . '/confirm_order_delivered',
      headers: $this->authBearerToken($this->user)
    );

    $response1->assertNotFound()
      ->assertJsonStructure(['message']);

    // Invalid order id
    $response2 = $this->putJson(
      '/api/orders/' . $order->id + 1 . '/confirm_order_delivered',
      headers: $this->authBearerToken($this->user)
    );

    $response2->assertNotFound()
      ->assertJsonStructure(['message']);
  }

  #[Test]
  public function cannot_confirm_order_delivered_if_order_status_is_not_on_delivery()
  {
    $order = Order::factory()
      ->for($this->user)
      ->create(['status' => Order::STATUS_PROCESSING]);

    $response = $this->putJson(
      '/api/orders/' . $order->id . '/confirm_order_delivered',
      headers: $this->authBearerToken($this->user)
    );

    $response->assertUnprocessable()
      ->assertJsonValidationErrors(['order_id']);
  }
}
