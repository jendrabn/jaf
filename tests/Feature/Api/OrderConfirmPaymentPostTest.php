<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\OrderController;
use App\Http\Requests\Api\ConfirmPaymentRequest;
use App\Models\{Invoice, Order, Payment, User};
use Database\Seeders\BankSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderConfirmPaymentPostTest extends TestCase
{
  use RefreshDatabase;

  private User $user;
  private array $data;

  protected function setUp(): void
  {
    parent::setUp();
    $this->seed(BankSeeder::class);
    $this->user = $this->createUser();
    $this->data = [
      'name' => 'BCA',
      'account_name' => 'Abdullah',
      'account_number' => '1988162520'
    ];
  }

  public function uri(int $orderId = 1): string
  {
    return '/api/orders/' . $orderId . '/confirm_payment';
  }

  /** @test */
  public function can_confirm_payment()
  {
    $order = Order::factory()
      ->for($this->user)
      ->afterCreating(
        fn ($order) => Invoice::factory(['due_date' => $order->created_at->addDays(1)])
          ->has(Payment::factory())
          ->for($order)
          ->create()
      )
      ->create(['status' => Order::STATUS_PENDING_PAYMENT]);

    $response = $this->postJson(
      $this->uri($order->id),
      $this->data,
      $this->authBearerToken($this->user)
    );

    $response->assertCreated()
      ->assertExactJson(['data' => true]);

    $this->assertDatabaseHas('payment_banks', $this->data);
    $this->assertTrue($order->fresh()->status === Order::STATUS_PENDING);
  }

  /** @test */
  public function unauthenticated_user_cannot_confirm_payment()
  {
    $response = $this->postJson($this->uri());

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function cannot_confirm_payment_if_order_doenot_exist()
  {
    $order = Order::factory()
      ->for($this->createUser())
      ->create();

    // Unauthorized order id
    $response1 = $this->postJson(
      $this->uri($order->id),
      $this->data,
      $this->authBearerToken($this->user)
    );

    $response1->assertNotFound()
      ->assertJsonStructure(['message']);

    // Invalid order id
    $response2 = $this->postJson(
      $this->uri($order->id + 1),
      $this->data,
      $this->authBearerToken($this->user)
    );

    $response2->assertNotFound()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function cannot_confirm_payment_if_order_status_is_not_pending_payment()
  {
    $order = Order::factory()
      ->for($this->user)
      ->afterCreating(
        fn ($order) => Invoice::factory(['due_date' => $order->created_at->addDays(1)])
          ->for($order)
          ->create()
      )
      ->create(['status' => Order::STATUS_PENDING]);

    $response = $this->postJson(
      $this->uri($order->id),
      $this->data,
      $this->authBearerToken($this->user)
    );

    $response->assertUnprocessable()
      ->assertJsonValidationErrors(['order_id']);
  }

  /** @test */
  public function cannot_confirm_payment_if_past_the_payment_due_date()
  {
    $order = Order::factory()
      ->for($this->user)
      ->afterCreating(
        fn ($order) => Invoice::factory(['due_date' => $order->created_at->addDays(1)])
          ->for($order)
          ->create()
      )
      ->create(['status' => Order::STATUS_PENDING_PAYMENT]);

    $this->travel(25)->hours();

    $response = $this->postJson(
      $this->uri($order->id),
      $this->data,
      $this->authBearerToken($this->user)
    );

    $response->assertUnprocessable()
      ->assertJsonValidationErrors(['order_id']);

    $this->assertTrue($order->fresh()->status === Order::STATUS_CANCELLED);
  }


  /** @test */
  public function confirm_payment_uses_the_correct_form_request()
  {
    $this->assertActionUsesFormRequest(
      OrderController::class,
      'confirmPayment',
      ConfirmPaymentRequest::class
    );
  }

  /** @test */
  public function confirm_payment_request_has_the_correct_validation_rules()
  {
    $this->assertValidationRules(
      [
        'name' => [
          'required',
          'string',
          'min:1',
          'max:50',
        ],
        'account_name' => [
          'required',
          'string',
          'min:1',
          'max:50',
        ],
        'account_number' => [
          'required',
          'string',
          'min:1',
          'max:50',
        ]
      ],
      (new ConfirmPaymentRequest())->rules()
    );
  }
}
