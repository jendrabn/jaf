<?php
// tests/Feature/Api/OrderByIdGetTest.php
namespace Tests\Feature\Api;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\User;
use Database\Seeders\BankSeeder;
use Database\Seeders\CitySeeder;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderByIdGetTest extends TestCase
{
  use RefreshDatabase;

  private string $uri = '/api/orders/';
  private User $user;

  protected function setUp(): void
  {
    parent::setUp();
    $this->user = $this->createUser();
  }

  /** @test */
  public function returns_unauthenticated_error_if_user_is_not_authenticated()
  {
    $response = $this->getJson($this->uri . 1, ['Authorization' => 'Bearer Invalid-Token']);

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function returns_not_found_error_if_order_id_doenot_exist()
  {
    $response = $this->getJson($this->uri . 1, $this->authBearerToken($this->user));

    $response->assertNotFound()
      ->assertJsonStructure(['message']);
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
      ->has(OrderItem::factory()->for(Product::factory()->create()), 'items')
      ->has(Invoice::factory()->has(Payment::factory()))
      ->has(Shipping::factory())
      ->for($this->user)
      ->create();

    $response = $this->getJson(
      $this->uri . $order->id,
      $this->authBearerToken($this->user)
    );

    $items = $order->items;
    $invoice = $order->invoice;
    $payment = $order->invoice->payment;
    $shippingAddress = $order->shipping->address;
    $shipping = $order->shipping;

    $response->assertOk()
      ->assertExactJson([
        'data' => [
          "id" => $order->id,
          "items" => $items->map(
            fn ($item) => [
              'id' => $item->id,
              'product' => $this->formatProductData($item->product),
              'name' => $item->name,
              'price' => $item->price,
              'weight' => $item->weight,
              'quantity' => $item->quantity,
            ]
          )->toArray(),
          "invoice" => [
            "id" => $invoice->id,
            "number" => $invoice->number,
            "amount" => $invoice->amount,
            "due_date" => $invoice->due_date,
            "status" => $invoice->status,
          ],
          "payment" => [
            "id" => $payment->id,
            "method" => $payment->method,
            "info" => [
              "name" => $payment->info['name'],
              "code" => $payment->info['code'],
              "account_name" => $payment->info['account_name'],
              "account_number" => $payment->info['account_number'],
            ],
            "amount" => $payment->amount,
            "status" => $payment->status
          ],
          "shipping_address" => [
            "name" => $shippingAddress['name'],
            "phone" => $shippingAddress['phone'],
            "province" => $shippingAddress['province'],
            "city" => $shippingAddress['city'],
            "district" => $shippingAddress['district'],
            "postal_code" => $shippingAddress['postal_code'],
            "address" => $shippingAddress['address']
          ],
          "shipping" => [
            "id" => $shipping->id,
            "courir" => $shipping->courir,
            "courier_name" => $shipping->courier_name,
            "service" => $shipping->service,
            "service_name" => $shipping->service_name,
            "etd" => $shipping->etd,
            "tracking_number" => $shipping->tracking_number,
            "status" => $shipping->status
          ],
          "notes" => $order->notes,
          "cancel_reason" => $order->cancel_reason,
          "status" => $order->status,
          "total_quantity" => $items->reduce(fn ($carry, $item) => $carry + $item->quantity),
          "total_weight" => $shipping->weight,
          "total_price" => $order->total_price,
          "shipping_cost" => $order->shipping_cost,
          "total_amount" => $invoice->amount,
          "payment_due_date" => $invoice->due_date,
          "confirmed_at" => $order->confirmed_at,
          "completed_at" => $order->completed_at,
          "cancelled_at" => $order->cancelled_at,
          "created_at" => $order->created_at,
        ]
      ]);
  }
}
