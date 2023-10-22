<?php

namespace Tests\Feature\Api;

use App\Models\{Invoice, Order, OrderItem, Payment, Product, Shipping, User};
use Database\Seeders\{BankSeeder, CitySeeder, ProductBrandSeeder, ProductCategorySeeder, ProvinceSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderDetailGetTest extends TestCase
{
  use RefreshDatabase;

  private User $user;

  protected function setUp(): void
  {
    parent::setUp();
    $this->user = $this->createUser();
  }

  private static function URI(int $orderId = 1): string
  {
    return "/api/orders/{$orderId}";
  }

  /** @test */
  public function can_get_order_detail_by_order_id()
  {
    $this->seed([
      ProductCategorySeeder::class,
      ProductBrandSeeder::class,
      ProvinceSeeder::class,
      CitySeeder::class,
      BankSeeder::class,
    ]);

    $order = Order::factory()
      ->has(OrderItem::factory()->for(Product::factory()->hasImages()->create()), 'items')
      ->has(Invoice::factory()->has(Payment::factory()))
      ->has(Shipping::factory())
      ->for($this->user)
      ->create();

    $response = $this->getJson(self::URI($order->id), headers: $this->authBearerToken($this->user));

    $response->assertOk()
      ->assertExactJson([
        'data' => [
          'id' => $order->id,
          'items' => $order->items->map(
            fn ($item) => [
              'id' => $item->id,
              'product' => $this->formatProductData($item->product),
              'name' => $item->name,
              'price' => $item->price,
              'weight' => $item->weight,
              'quantity' => $item->quantity,
            ]
          )->toArray(),
          'invoice' => [
            'id' => $order->invoice->id,
            'number' => $order->invoice->number,
            'amount' => $order->invoice->amount,
            'due_date' => $order->invoice->due_date,
            'status' => $order->invoice->status,
          ],
          'payment' => [
            'id' => $order->invoice->payment->id,
            'method' => $order->invoice->payment->method,
            'info' => [
              'name' => $order->invoice->payment->info['name'],
              'code' => $order->invoice->payment->info['code'],
              'account_name' => $order->invoice->payment->info['account_name'],
              'account_number' => $order->invoice->payment->info['account_number'],
            ],
            'amount' => $order->invoice->payment->amount,
            'status' => $order->invoice->payment->status
          ],
          'shipping_address' => [
            'name' => $order->shipping->address['name'],
            'phone' => $order->shipping->address['phone'],
            'province' => $order->shipping->address['province'],
            'city' => $order->shipping->address['city'],
            'district' => $order->shipping->address['district'],
            'postal_code' => $order->shipping->address['postal_code'],
            'address' => $order->shipping->address['address']
          ],
          'shipping' => [
            'id' => $order->shipping->id,
            'courir' => $order->shipping->courir,
            'courier_name' => $order->shipping->courier_name,
            'service' => $order->shipping->service,
            'service_name' => $order->shipping->service_name,
            'etd' => $order->shipping->etd,
            'tracking_number' => $order->shipping->tracking_number,
            'status' => $order->shipping->status
          ],
          'notes' => $order->notes,
          'cancel_reason' => $order->cancel_reason,
          'status' => $order->status,
          'total_quantity' => $order->items->reduce(fn ($carry, $item) => $carry + $item->quantity),
          'total_weight' => $order->shipping->weight,
          'total_price' => $order->total_price,
          'shipping_cost' => $order->shipping_cost,
          'total_amount' => $order->invoice->amount,
          'payment_due_date' => $order->invoice->due_date,
          'confirmed_at' => $order->confirmed_at,
          'completed_at' => $order->completed_at,
          'cancelled_at' => $order->cancelled_at,
          'created_at' => $order->created_at,
        ]
      ]);

    $this->assertStringStartsWith('http', $response['data']['items'][0]['product']['image']);
  }

  /** @test */
  public function returns_unauthenticated_error_if_user_is_not_authenticated()
  {
    $response = $this->getJson(self::URI());

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function returns_not_found_error_if_order_doenot_exist()
  {
    $this->seed([
      ProductCategorySeeder::class,
      ProductBrandSeeder::class,
      ProvinceSeeder::class,
      CitySeeder::class,
      BankSeeder::class,
    ]);

    $order = Order::factory()
      ->has(OrderItem::factory()->for($this->createProduct()), 'items')
      ->has(Invoice::factory()->has(Payment::factory()))
      ->has(Shipping::factory())
      ->for($this->createUser())
      ->create();

    // Unauthorized order id
    $response1 = $this->getJson(self::URI($order->id), headers: $this->authBearerToken($this->user));

    $response1->assertNotFound()
      ->assertJsonStructure(['message']);

    // Invalid order id
    $response2 = $this->getJson(self::URI($order->id + 1), headers: $this->authBearerToken($this->user));

    $response2->assertNotFound()
      ->assertJsonStructure(['message']);
  }
}
