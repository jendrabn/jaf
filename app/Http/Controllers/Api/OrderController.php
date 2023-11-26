<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\{ConfirmPaymentRequest, CreateOrderRequest};
use App\Http\Resources\{OrderCollection, OrderDetailResource};
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

    return OrderCollection::make($orders)
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function create(CreateOrderRequest $request): JsonResponse
  {
    $order = $this->orderService->createOrder($request);

    return response()->json([
      'data' => [
        'id' => $order->id,
        'total_amount' =>  $order->invoice->amount,
        'payment_method' =>  $order->invoice->payment->method,
        'payment_info' =>  $order->invoice->payment->info,
        'payment_due_date' =>  $order->invoice->due_date,
        'created_at' => $order->created_at
      ]
    ], Response::HTTP_CREATED);
  }

  public function get(Order $order): JsonResponse
  {
    throw_if($order->user_id !== auth()->id(), ModelNotFoundException::class);

    $order->load([
      'items',
      'items.product',
      'items.product.category',
      'items.product.brand',
      'invoice',
      'invoice.payment',
      'shipping'
    ]);

    return OrderDetailResource::make($order)
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function confirmPayment(ConfirmPaymentRequest $request, Order $order)
  {
    $this->orderService->confirmPayment($request, $order);

    return response()->json(['data' => true], Response::HTTP_CREATED);
  }

  public function confirmDelivered(Order $order): JsonResponse
  {
    $this->orderService->confirmDelivered($order);

    return response()->json(['data' => true], Response::HTTP_OK);
  }
}
