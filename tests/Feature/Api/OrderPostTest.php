<?php
// tests/Feature/Api/OrderPostTest.php
namespace Tests\Feature\Api;

use App\Http\Controllers\Api\OrderController;
use App\Http\Requests\Api\CreateOrderRequest;
use App\Models\Bank;
use App\Models\Cart;
use App\Models\Invoice;
use App\Models\Order;
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
use Illuminate\Testing\TestResponse;
use Illuminate\Validation\Rule;
use Tests\TestCase;

class OrderPostTest extends TestCase
{
  use RefreshDatabase;

  private $uri = '/api/orders';
  private User $user;
  private Bank $bank;
  private array $payload;

  protected function setUp(): void
  {
    parent::setUp();
    $this->seed([
      ProductCategorySeeder::class,
      ProductBrandSeeder::class,
      BankSeeder::class,
      ProvinceSeeder::class,
      CitySeeder::class
    ]);
    $this->user = $this->createUser();
    $this->bank = Bank::first();
    $this->payload = [
      'cart_ids' => [],
      'shipping_address' => [
        'name' => 'Garfield',
        'phone' => '+6282310009788',
        'city_id' => 154,
        'district' => 'Cipayung',
        'postal_code' => '13845',
        'address' => 'Jl. Belimbing XII No.19'
      ],
      'shipping_courier' => 'jne',
      'shipping_service' => 'REG',
      'payment_method' => 'bank',
      'bank_id' => $this->bank->id,
      'notes' => fake()->sentence()
    ];
  }

  /** @test */
  public function can_create_order()
  {
    $cart1 = $this->createCart(2, ['price' => 25000, 'stock' => 5, 'weight' => 500]);
    $cart2 = $this->createCart(1, ['price' => 50000, 'stock' => 5, 'weight' => 500]);
    $totalWeight = 1500;
    $totalPrice = 100000;
    $shippingCost = 34000;
    $totalAmount = $totalPrice + $shippingCost;

    $response = $this->attemptToCreateOrder(['cart_ids' => [$cart1->id, $cart2->id]]);

    $this->assertDatabaseCount('orders', 1);

    $order = Order::first();
    $paymentDueDate = $order['created_at']->addDays(1);

    $response->assertCreated()
      ->assertExactJson([
        'data' => [
          'id' => $order['id'],
          'total_amount' => $totalAmount,
          'payment_method' => $this->payload['payment_method'],
          'payment_info' => [
            'name' => $this->bank['name'],
            'code' => $this->bank['code'],
            'account_name' => $this->bank['account_name'],
            'account_number' => $this->bank['account_number']
          ],
          'payment_due_date' => $paymentDueDate->toISOString(),
          'created_at' => $order['created_at']->toISOString()
        ]
      ]);

    $response = $response['data'];

    $this->assertDatabaseHas('orders', [
      'id' => $response['id'],
      'total_price' => $totalPrice,
      'shipping_cost' => $shippingCost,
      'notes' => $this->payload['notes'],
      'status' => Order::STATUS_PENDING_PAYMENT,
    ]);

    $this->assertDatabaseHas('order_items', [
      'order_id' => $response['id'],
      'product_id' => $cart1['id'],
      'name' => $cart1['product']['name'],
      'weight' => $cart1['product']['weight'],
      'price' => $cart1['product']['price'],
      'quantity' => $cart1['quantity'],
    ]);

    $this->assertDatabaseHas('order_items', [
      'order_id' => $response['id'],
      'product_id' => $cart2['id'],
      'name' => $cart2['product']['name'],
      'weight' => $cart2['product']['weight'],
      'price' => $cart2['product']['price'],
      'quantity' => $cart2['quantity'],
    ]);

    $this->assertDatabaseHas('invoices', [
      'order_id' => $response['id'],
      'number' => sprintf('INV/%s/%s', $order['created_at']->format('YYYYMMDD'), $response['id']),
      'amount' => $totalAmount,
      'status' => Invoice::STATUS_UNPAID,
      'due_date' => $paymentDueDate,
    ]);

    $this->assertDatabaseHas('payments', [
      'method' => $this->payload['payment_method'],
      'info' => json_encode([
        'name' => $this->bank['name'],
        'code' => $this->bank['code'],
        'account_name' => $this->bank['account_name'],
        'account_number' => $this->bank['account_number']
      ]),
      'amount' => $totalAmount,
      'status' => Payment::STATUS_PENDING
    ]);

    $shippingAddress = $this->payload['shipping_address'];
    $this->assertDatabaseHas('shippings', [
      'order_id' => $response['id'],
      'address' => json_encode([
        'name' => $shippingAddress['name'],
        'phone' => $shippingAddress['phone'],
        'province' => 'DKI Jakarta',
        'city' => 'Jakarta Timur',
        'district' => $shippingAddress['district'],
        'postal_code' => $shippingAddress['postal_code'],
        'address' => $shippingAddress['address']
      ]),
      'courier' => $this->payload['shipping_courier'],
      'courier_name' => 'Jalur Nugraha Ekakurir (JNE)',
      'service' => $this->payload['shipping_service'],
      'service_name' => 'Layanan Reguler',
      'etd' => '1-2 hari',
      'weight' => $totalWeight,
      'status' => Shipping::STATUS_PENDING
    ]);

    $this->assertDatabaseMissing('carts', ['id' => $cart1->id]);
    $this->assertDatabaseMissing('carts', ['id' => $cart2->id]);

    $this->assertDatabaseHas('products', [
      'id' => $cart1['product']['id'],
      'stock' => 3
    ]);

    $this->assertDatabaseHas('products', [
      'id' => $cart2['product']['id'],
      'stock' => 4
    ]);
  }

  /** @test */
  public function cannot_create_order_if_shipping_service_is_invalid()
  {
    $cart1 = $this->createCart(2, ['price' => 25000, 'stock' => 5, 'weight' => 500]);
    $cart2 = $this->createCart(1, ['price' => 50000, 'stock' => 5, 'weight' => 500]);

    $this->attemptToCreateOrderAndExpectFail422(
      'shipping_service',
      ['cart_ids' => [$cart1->id, $cart2->id], 'shipping_service' => 'INVALID'],
    );
  }

  /** @test */
  public function cannot_create_order_if_product_is_not_published()
  {
    $cart1 = $this->createCart(1, ['stock' => 1, 'weight' => 100, 'is_publish' => false]);
    $cart2 = $this->createCart(3, ['stock' => 3, 'weight' => 100]);

    $this->attemptToCreateOrderAndExpectFail422(
      'product',
      ['cart_ids' => [$cart1->id, $cart2->id]],
    );
    $this->assertDatabaseMissing('carts', $cart1->toArray());
  }

  /** @test */
  public function cannot_create_order_if_quantity_exceed_stock()
  {
    $cart1 = $this->createCart(3, ['stock' => 1, 'weight' => 100]);
    $cart2 = $this->createCart(1, ['stock' => 3, 'weight' => 100]);

    $this->attemptToCreateOrderAndExpectFail422(
      'cart',
      ['cart_ids' => [$cart1->id, $cart2->id]],
    );
  }

  /** @test */
  public function cannot_create_order_if_total_weight_exceed_25kg()
  {
    $cart1 = $this->createCart(2, ['stock' => 5, 'weight' => 3000]);
    $cart2 = $this->createCart(2, ['stock' => 5, 'weight' => 10000]);

    $this->attemptToCreateOrderAndExpectFail422(
      'cart',
      ['cart_ids' => [$cart1->id, $cart2->id]],
    );
  }

  /** @test */
  public function returns_unauthenticated_error_if_user_is_not_authenticated()
  {
    $response = $this->getJson($this->uri, [
      'Authorization' => 'Bearer Invalid-Token'
    ]);

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function create_order_uses_the_correct_form_request()
  {
    $this->assertActionUsesFormRequest(
      OrderController::class,
      'create',
      CreateOrderRequest::class
    );
  }

  /** @test */
  public function create_order_request_uses_the_correct_validation_rules()
  {
    $rules = (new CreateOrderRequest())
      ->setUserResolver(fn () => $this->user)
      ->rules();

    $this->assertValidationRules(
      [
        'cart_ids' => [
          'required',
          'array',
        ],
        'cart_ids.*' => [
          'required',
          'integer',
          Rule::exists('carts', 'id')->where('user_id', $this->user->id)
        ],
        'shipping_address.name' => [
          'required',
          'string',
          'min:1',
          'max:30',
        ],
        'shipping_address.phone' => [
          'required',
          'string',
          'starts_with:08,62,+62',
          'min:10',
          'max:15',
        ],
        'shipping_address.city_id' => [
          'required',
          'integer',
          'exists:cities,id',
        ],
        'shipping_address.district' => [
          'required',
          'string',
          'min:1',
          'max:100',
        ],
        'shipping_address.postal_code' => [
          'required',
          'string',
          'min:5',
          'max:5',
        ],
        'shipping_address.address' => [
          'required',
          'string',
          'min:1',
          'max:255',
        ],
        'shipping_courier' => [
          'required',
          'string',
          Rule::in(Shipping::COURIERS)
        ],
        'shipping_service' => [
          'required',
          'string'
        ],
        'payment_method' => [
          'required',
          'string',
          'in:bank',
        ],
        'bank_id' => [
          'required',
          'integer',
          'exists:banks,id',
        ],
        'notes' => [
          'nullable',
          'string',
          'max:200',
        ]
      ],
      $rules
    );
  }

  public function createCart(int $quantity = 1, ?array $productData = []): Cart
  {
    return Cart::factory()
      ->for(Product::factory()->create($productData))
      ->for($this->user)
      ->create(compact('quantity'));
  }

  public function attemptToCreateOrder(array $data = []): TestResponse
  {
    $response = $this->postJson(
      $this->uri,
      array_merge($this->payload, $data),
      $this->authBearerToken($this->user)
    );

    return $response;
  }

  public function attemptToCreateOrderAndExpectFail422(array|string $error, ?array $data = [],): TestResponse
  {
    $response = $this->attemptToCreateOrder($data);

    $response->assertUnprocessable()
      ->assertJsonValidationErrors($error);

    return $response;
  }
}
