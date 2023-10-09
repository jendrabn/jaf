<?php

// app/Http/Controllers/Api/CheckoutController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\{CheckoutRequest, ShippingCostRequest};
use App\Http\Resources\{BankResource, CartResource, UserAddressResource};
use App\Services\{OrderService, RajaOngkirService};
use App\Models\{Bank, Cart, Shipping};
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
    $validatedData = $request->validated();
    $carts = Cart::whereIn('id', $validatedData['cart_ids'])->get();

    $this->orderService->validateBeforeCreateOrder($carts);

    $user = auth()->user();
    $userAddress = $user->address;
    $totalWeight = $this->orderService->getTotalWeight($carts);
    $totalQuantity = $this->orderService->getTotalQuantity($carts);
    $totalPrice = $this->orderService->getTotalPrice($carts);
    $shippingCosts = $userAddress
      ? $this->rajaOngkirService->getCosts(
        $userAddress->city_id,
        $totalWeight
      )
      : NULL;

    return response()
      ->json([
        'data' => [
          'shipping_address' => $userAddress
            ? (new UserAddressResource($userAddress))
            : NULL,
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
    $validatedData = $request->validated();
    $shippingCosts = $this->rajaOngkirService->getCosts(
      $validatedData['destination'],
      $validatedData['weight']
    );

    return response()->json(['data' => $shippingCosts], Response::HTTP_OK);
  }
}
