<?php

// app/Http/Controllers/Api/CheckoutController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckoutRequest;
use App\Http\Requests\Api\ShippingCostRequest;
use App\Http\Resources\BankResource;
use App\Http\Resources\CartResource;
use App\Http\Resources\UserAddressResource;
use App\Http\Services\CartService;
use App\Http\Services\RajaOngkirService;
use App\Models\Bank;
use App\Models\Cart;
use App\Models\Shipping;
use App\Models\UserAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController extends Controller
{
  public function checkout(CartService $cartService, RajaOngkirService $rajaOngkirService, CheckoutRequest $request): JsonResponse
  {
    $cartIds = $request->validated('cart_ids');
    $carts = Cart::whereIn('id', $cartIds)->get();
    $user = auth()->user();
    $userAddress = UserAddress::where('user_id', $user->id)->first();

    foreach ($carts as $cart) {
      $cartService->validateProduct($cart);
      $cartService->validateQuantity($cart);
    }

    list($totalQuantity, $totalWeight, $totalPrice) = $cartService->getTotals($carts);

    throw_if(
      $totalWeight > Shipping::MAX_WEIGHT,
      ValidationException::withMessages([
        'cart' => 'The total weight must not be greater than 25kg.'
      ])
    );

    $banks = Bank::all();
    $shippingCosts = $userAddress
      ? $rajaOngkirService->getCosts($userAddress->city_id, $totalWeight)
      : null;

    return response()->json([
      'data' => [
        'shipping_address' => $userAddress
          ? (new UserAddressResource($userAddress))
          : null,
        'carts' => CartResource::collection($carts),
        'shipping_methods' => $shippingCosts,
        'payment_methods' => [
          'bank' => BankResource::collection($banks)
        ],
        'total_quantity' => $totalQuantity,
        'total_weight' => $totalWeight,
        'total_price' => $totalPrice,
      ]
    ])->setStatusCode(Response::HTTP_OK);
  }

  public function shippingCost(ShippingCostRequest $request, RajaOngkirService $rajaOngkirService): JsonResponse
  {
    $validatedData = $request->validated();
    $costs = $rajaOngkirService->getCosts($validatedData['destination'], $validatedData['weight']);

    return response()
      ->json(['data' => $costs])
      ->setStatusCode(Response::HTTP_OK);
  }
}
