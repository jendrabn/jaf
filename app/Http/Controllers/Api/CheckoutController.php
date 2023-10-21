<?php

// app/Http/Controllers/Api/CheckoutController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\{CheckoutRequest, ShippingCostRequest};
use App\Http\Resources\{BankResource, CartResource, UserAddressResource};
use App\Services\{OrderService, RajaOngkirService};
use App\Models\{Bank, Cart};
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController extends Controller
{
  public function __construct(
    private RajaOngkirService $rajaOngkirService,
    private OrderService $orderService,
  ) {
  }

  public function checkout(CheckoutRequest $request): JsonResponse
  {
    $carts = Cart::whereIn('id', $request->validated('cart_ids'))->get();

    $this->orderService->validateBeforeCreateOrder($carts);

    $userAddress = auth()->user()->address;
    $totalWeight = $this->orderService->totalWeight($carts);
    $totalQuantity = $this->orderService->totalQuantity($carts);
    $totalPrice = $this->orderService->totalPrice($carts);
    $shippingCosts = $userAddress
      ? $this->rajaOngkirService->getCosts($userAddress->city_id, $totalWeight)
      : null;

    return response()->json([
      'data' => [
        'shipping_address' => $userAddress ? new UserAddressResource($userAddress) : null,
        'carts' => CartResource::collection($carts),
        'shipping_methods' => $shippingCosts,
        'payment_methods' => ['bank' => BankResource::collection(Bank::all())],
        'total_quantity' => $totalQuantity,
        'total_weight' => $totalWeight,
        'total_price' => $totalPrice,
      ]
    ], Response::HTTP_OK);
  }

  public function shippingCost(ShippingCostRequest $request): JsonResponse
  {
    $shippingCosts = $this->rajaOngkirService->getCosts(...$request->validated());

    return response()->json(['data' => $shippingCosts], Response::HTTP_OK);
  }
}
