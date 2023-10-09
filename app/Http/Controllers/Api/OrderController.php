<?php

// app/Http/Controllers/Api/OrderController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\{ConfirmPaymentRequest, CreateOrderRequest};
use App\Http\Resources\{
  InvoiceResource,
  OrderCollection,
  OrderItemResource,
  PaymentResource,
  ShippingResource
};
use App\Services\OrderService;
use App\Models\Order;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
  public function __construct(private OrderService $orderService)
  {
  }

  public function list(Request $request): JsonResponse
  {
    $orders = $this->orderService->getOrders($request);

    return (new OrderCollection($orders))
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function create(CreateOrderRequest $request): JsonResponse
  {
    $order = $this->orderService->createOrder($request);
    $invoice = $order->invoice;

    return response()
      ->json([
        'data' => [
          'id' => $order->id,
          'total_amount' => $invoice->amount,
          'payment_method' => $invoice->payment->method,
          'payment_info' => $invoice->payment->info,
          'payment_due_date' => $invoice->due_date,
          'created_at' => $order->created_at
        ]
      ], Response::HTTP_CREATED);
  }

  public function get(int $id): JsonResponse
  {
    $user = auth()->user();
    $order = $user->orders()->findOrFail($id);

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
      ], Response::HTTP_OK);
  }

  public function confirmPayment(ConfirmPaymentRequest $request, int $id)
  {
    $this->orderService->confirmPayment($request, $id);

    return response()->json(['data' => true], Response::HTTP_CREATED);
  }

  public function confirmDelivered(int $id): JsonResponse
  {
    $this->orderService->confirmDelivered($id);

    return response()->json(['data' => true], Response::HTTP_OK);
  }
}
