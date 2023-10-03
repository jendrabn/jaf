<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderCollection;
use App\Http\Services\OrderService;
use Illuminate\Http\Request;
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
}
