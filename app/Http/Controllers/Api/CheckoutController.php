<?php
// app/Http/Controllers/Api/CheckoutController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckoutRequest;
use App\Http\Requests\Api\ShippingCostRequest;
use App\Http\Resources\BankResource;
use App\Http\Resources\CartResource;
use App\Http\Resources\UserAddressResource;
use App\Services\CartService;
use App\Services\RajaOngkirService;
use App\Models\Bank;
use App\Models\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;


class CheckoutController extends Controller
{
  public function checkout(
    CartService $cartService,
    RajaOngkirService $rajaOngkirService,
    CheckoutRequest $request
  ): JsonResponse {

    $user = auth()->user();
    $cartIds = $request->validated('cart_ids');
    $carts = Cart::whereIn('id', $cartIds)->get();

    foreach ($carts as $cart) {
      throw_unless(
        $cartService->validateProduct($cart),
        ValidationException::withMessages([
          'product' => 'Produk yang diinginkan sedang tidak tersedia.'
        ])
      );

      throw_unless(
        $cartService->validateQuantity($cart),
        ValidationException::withMessages([
          'cart' => 'Kuantitas melebihi stok yang tersedia.'
        ])
      );
    }

    $totalWeight = $cartService->getTotalWeight($carts);

    throw_unless(
      $cartService->validateTotalWeight($totalWeight),
      ValidationException::withMessages([
        'cart' => 'Berat total tidak boleh lebih dari 25kg.'
      ])
    );

    $userAddress = $user->address;
    $shippingCosts = $userAddress
      ? $rajaOngkirService->getCosts($userAddress->city_id, $totalWeight)
      : null;
    $banks = Bank::all();

    return response()
      ->json([
        'data' => [
          'shipping_address' => $userAddress ? (new UserAddressResource($userAddress)) : null,
          'carts' => CartResource::collection($carts),
          'shipping_methods' => $shippingCosts,
          'payment_methods' => ['bank' => BankResource::collection($banks)],
          'total_quantity' => $cartService->getTotalQuantity($carts),
          'total_weight' => $totalWeight,
          'total_price' => $cartService->getTotalPrice($carts),
        ]
      ])
      ->setStatusCode(Response::HTTP_OK);
  }

  public function shippingCost(ShippingCostRequest $request, RajaOngkirService $rajaOngkirService): JsonResponse
  {
    $validatedData = $request->validated();
    $results = $rajaOngkirService->getCosts($validatedData['destination'], $validatedData['weight']);

    return response()
      ->json(['data' => $results])
      ->setStatusCode(Response::HTTP_OK);
  }
}
