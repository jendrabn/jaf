<?php
// app\Http\Controllers\Api\OrderController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ConfirmPaymentRequest;
use App\Http\Requests\Api\CreateOrderRequest;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderItemResource;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\ShippingResource;
use App\Http\Services\CartService;
use App\Http\Services\OrderService;
use App\Http\Services\RajaOngkirService;
use App\Models\Bank;
use App\Models\Cart;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Shipping;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
  public function list(Request $request, OrderService $orderService): JsonResponse
  {
    return (new OrderCollection($orderService->getOrders($request)))
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function create(CreateOrderRequest $request, CartService $cartService, RajaOngkirService $rajaOngkirService): JsonResponse
  {
    $validatedData = $request->validated();
    $user = auth()->user();
    $carts = $user->carts()->whereIn('id', $validatedData['cart_ids'])->get();
    $bank = Bank::where('id', $validatedData['bank_id'])->firstOrFail();
    $shippingAddress = $validatedData['shipping_address'];

    $carts->each(
      function ($cart) use ($cartService) {
        $cartService->validateProduct($cart);
        $cartService->validateQuantity($cart);
      }
    );

    list($totalQuantity, $totalWeight, $totalPrice) = $cartService->getTotals($carts);

    throw_if(
      $totalWeight > Shipping::MAX_WEIGHT,
      ValidationException::withMessages([
        'cart' => 'Berat total pesanan tidak boleh lebih dari 25kg.'
      ])
    );

    $shippingService = $rajaOngkirService
      ->getService(
        $validatedData['shipping_service'],
        $shippingAddress['city_id'],
        $totalWeight,
        $validatedData['shipping_courier']
      );

    throw_if(
      !$shippingService,
      ValidationException::withMessages([
        'shipping_service' => 'Layanan kurir tidak tersedia.'
      ])
    );

    $totalAmount = $shippingService['cost'] + $totalPrice;

    DB::beginTransaction();

    try {
      $order = Order::create([
        'user_id' => $user->id,
        'total_price' => $totalPrice,
        'shipping_cost' => $shippingService['cost'],
        'status' => Order::STATUS_PENDING_PAYMENT,
        'notes' => $validatedData['notes'],
      ]);

      foreach ($carts as $cart) {
        $product = $cart->product;

        OrderItem::create([
          'order_id' => $order->id,
          'product_id' => $product->id,
          'name' => $product->name,
          'weight' => $product->weight,
          'price' => $product->price,
          'quantity' => $cart->quantity,
        ]);

        $product->decrement('stock', $cart->quantity);
        $cart->delete();
      }

      $invoice = Invoice::create([
        'order_id' => $order->id,
        'number' => implode('/', [
          'INV', $order->created_at->format('YYYYMMDD'), $order->id
        ]),
        'amount' => $totalAmount,
        'status' => Invoice::STATUS_UNPAID,
        'due_date' => $order->created_at->addDays(1),
      ]);

      $payment = Payment::create([
        'invoice_id' => $invoice->id,
        'method' => $validatedData['payment_method'],
        'info' => [
          'name' => $bank->name,
          'code' => $bank->code,
          'account_name' => $bank->account_name,
          'account_number' => $bank->account_number
        ],
        'amount' => $totalAmount,
        'status' => Payment::STATUS_PENDING
      ]);

      $userAddress = $user->address()->updateOrCreate($shippingAddress);

      Shipping::create([
        'order_id' => $order->id,
        'address' => [
          'name' =>  $userAddress->name,
          'phone' =>  $userAddress->phone,
          'province' => $userAddress->city->province->name,
          'city' => $userAddress->city->name,
          'district' =>  $userAddress->district,
          'postal_code' =>  $userAddress->postal_code,
          'address' =>  $userAddress->address
        ],
        'courier' => $shippingService['courier'],
        'courier_name' => $shippingService['courier_name'],
        'service' => $shippingService['service'],
        'service_name' => $shippingService['service_name'],
        'etd' => $shippingService['etd'],
        'weight' => $totalWeight,
        'status' => Shipping::STATUS_PENDING,
      ]);

      DB::commit();
    } catch (QueryException $e) {
      DB::rollBack();
      throw $e;
    }

    return response()
      ->json([
        'data' => [
          'id' => $order->id,
          'total_amount' => $totalAmount,
          'payment_method' => $payment->method,
          'payment_info' => $payment->info,
          'payment_due_date' => $invoice->due_date->toISOString(),
          'created_at' => $order->created_at
        ]
      ])
      ->setStatusCode(Response::HTTP_CREATED);
  }

  public function get(Order $order): JsonResponse
  {
    return response()
      ->json([
        'data' => [
          'id' => $order->id,
          'items' => OrderItemResource::collection($order->items),
          'invoice' => (new InvoiceResource($order->invoice)),
          'payment' => (new PaymentResource($order->invoice->payment)),
          'shipping_address' => $order->shipping->address,
          'shipping' => (new ShippingResource($order->shipping)),
          'notes' => $order->notes,
          'cancel_reason' => $order->cancel_reason,
          'status' => $order->status,
          'total_quantity' => $order->total_quantity,
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
      ])->setStatusCode(Response::HTTP_OK);
  }

  public function confirmPayment(ConfirmPaymentRequest $request, int $id)
  {
    $order = Order::where('user_id', auth()->id())->findOrFail($id);

    if ($order->status !== Order::STATUS_PENDING_PAYMENT) {
      throw ValidationException::withMessages(['order' => 'Invalid order.']);
    }

    if (now()->isAfter($order->invoice->due_date)) {
      $order->status = Order::STATUS_CANCELLED;
      $order->save();

      throw ValidationException::withMessages(['order' => 'Invalid order.']);
    }

    try {
      DB::beginTransaction();

      $order->invoice->payment->bank()->create($request->validated());
      $order->status = Order::STATUS_PENDING;
      $order->save();

      DB::commit();
    } catch (QueryException $e) {
      DB::rollBack();

      throw $e;
    }

    return response()->json(['data' => true])->setStatusCode(Response::HTTP_CREATED);
  }
}
