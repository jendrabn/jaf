<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\{ConfirmPaymentRequest, CreateOrderRequest};
use App\Http\Resources\{OrderCollection, OrderDetailResource};
use App\Services\OrderService;
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

  public function get(int $order_id): JsonResponse
  {
    $order = auth()->user()->orders()->findOrFail($order_id);

    return (new OrderDetailResource($order))
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function confirmPayment(ConfirmPaymentRequest $request, int $order_id)
  {
    $this->orderService->confirmPayment($request, $order_id);

    return response()->json(['data' => true], Response::HTTP_CREATED);
  }

  public function confirmDelivered(int $order_id): JsonResponse
  {
    $this->orderService->confirmDelivered($order_id);

    return response()->json(['data' => true], Response::HTTP_OK);
  }
}
