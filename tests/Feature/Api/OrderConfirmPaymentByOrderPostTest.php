<?php
// tests/Feature/Api/OrderConfirmPaymentByOrderPostTest.php
namespace Tests\Feature\Api;

use App\Http\Controllers\Api\OrderController;
use App\Http\Requests\Api\ConfirmPaymentRequest;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\BankSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderConfirmPaymentByOrderPostTest extends TestCase
{
  use RefreshDatabase;

  private User $user;
  private array $payload;

  protected function setUp(): void
  {
    parent::setUp();
    $this->seed([BankSeeder::class]);
    $this->user = $this->createUser();
    $this->payload = [
      'name' => 'BCA',
      'account_name' => 'John Lennon',
      'account_number' => '1988162520'
    ];
  }

  /** @test */
  public function returns_unauthenticated_error_if_user_is_not_authenticated()
  {
    $response = $this->getJson(
      '/api/orders/1/confirm_payment',
      ['Authorization' => 'Bearer Invalid-Token']
    );

    $response->assertUnauthorized()
      ->assertJsonStructure(['message']);
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
    $this->assertValidationRules([
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
    ], (new ConfirmPaymentRequest())->rules());
  }

  /** @test */
  public function returns_not_found_error_if_order_id_doenot_exist()
  {
    $order = Order::factory()->for(User::factory())->create();

    $response1 = $this->attemptToConfirmPayment($order->id + 1);

    $response1->assertNotFound()
      ->assertJsonStructure(['message']);

    $response2 = $this->attemptToConfirmPayment();

    $response2->assertNotFound()
      ->assertJsonStructure(['message']);
  }

  /** @test */
  public function cannot_confirm_payment_if_order_status_is_not_payment_pending()
  {
    $order = Order::factory()->for($this->user)
      ->create(['status' => Order::STATUS_PENDING]);
    Invoice::factory(['due_date' => $order->created_at->addDays(1)])
      ->for($order)->create();

    $response = $this->attemptToConfirmPayment();

    $response->assertUnprocessable()
      ->assertJsonStructure(['message', 'errors' => ['*' => []]])
      ->assertJsonValidationErrorFor('order');

    $this->assertTrue($order->fresh()->status === Order::STATUS_CANCELLED);
  }

  /** @test */
  public function cannot_confirm_payment_if_past_the_payment_due_date()
  {
    $order = Order::factory()->for($this->user)
      ->create(['status' => Order::STATUS_PENDING_PAYMENT]);
    Invoice::factory(['due_date' => $order->created_at->addDays(1)])
      ->for($order)->create();

    $this->travel(25)->hours();

    $response = $this->attemptToConfirmPayment();

    $response->assertUnprocessable()
      ->assertJsonStructure(['message', 'errors' => ['*' => []]])
      ->assertJsonValidationErrorFor('order');

    $this->assertTrue($order->fresh()->status === Order::STATUS_CANCELLED);
  }

  /** @test */
  public function can_confirm_payment()
  {
    $order = Order::factory()->for($this->user)
      ->create(['status' => Order::STATUS_PENDING_PAYMENT]);
    $invoice = Invoice::factory(['due_date' => $order->created_at->addDays(1)])
      ->has(Payment::factory())
      ->for($order)->create();

    $response = $this->attemptToConfirmPayment();

    $response->assertCreated()
      ->assertExactJson(['data' => true]);

    $this->assertDatabaseHas('payment_banks', [
      'payment_id' => $invoice->payment->id
    ] + $this->payload);
    $this->assertTrue($order->fresh()->status === Order::STATUS_PENDING);
  }

  private function attemptToConfirmPayment(int $id = 1)
  {
    $response = $this->postJson(
      '/api/orders/' . $id . '/confirm_payment',
      $this->payload,
      $this->authBearerToken($this->user),
    );

    return $response;
  }
}
