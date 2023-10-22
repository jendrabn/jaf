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

  public function get(int $id): JsonResponse
  {
    $order = auth()->user()->orders()->findOrFail($id);

    return OrderDetailResource::make($order)
      ->response()
      ->setStatusCode(Response::HTTP_OK);
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
