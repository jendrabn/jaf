<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckoutRequest;
use App\Models\Bank;
use App\Models\Cart;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
  public function checkout(CheckoutRequest $request)
  {
    $carts = Cart::whereIn('id', $request->validated('cart_ids'))->get();
    $userAddress = UserAddress::where('user_id', auth()->id())->first();
    $totalQuantity = 0;
    $totalWeight = 0;
    $totalPrice = 0;

    foreach ($carts as $cart) {
      if (!$cart->product->is_publish) {
        $cart->delete();
        throw ValidationException::withMessages([
          'product' => 'The product must be published.'
        ]);
      }

      if ($cart->quantity > $cart->product->stock) {
        throw ValidationException::withMessages([
          'cart' => sprintf('The quantity [ID%s] must not be greater than stock.', $cart->id)
        ]);
      }

      $totalQuantity = $totalQuantity + $cart->quantity;
      $totalWeight = $totalWeight + ($cart->quantity * $cart->product->weight);
      $totalPrice = $totalPrice + ($cart->quantity * $cart->product->price);
    }

    if ($totalWeight > 25000) {
      throw ValidationException::withMessages([
        'cart' => 'The total weight must not be greater than 25kg.'
      ]);
    }

    $costs = [];
    if ($userAddress) {
      $couriers = ['jne', 'tiki', 'pos'];

      foreach ($couriers as $courier) {
        $ongkirResponse = Http::withHeaders([
          'Accept' => 'application/json',
          'key' => '99600a604c31c5da4d33700efeb375e3'
        ])->post('https://api.rajaongkir.com/starter/cost', [
          'origin' => config('shop.address.city_id'),
          'destination' =>  $userAddress->city_id,
          'weight' => $totalWeight,
          'courier' => $courier
        ]);

        $ongkirResponse->throwIf(!$ongkirResponse->ok());

        $results =  $ongkirResponse->object()->rajaongkir->results[0] ?? [];
        if ($results && $results->costs) {
          array_push(
            $costs,
            ...collect($results->costs)
              ->map(fn ($cost) => [
                'courier' => $courier,
                'courier_name' => $results->name,
                'service' => $cost->service,
                'service_name' => $cost->description,
                'cost' => $cost->cost[0]->value,
                'etd' => str_replace(['hari', ' '], '', strtolower($cost->cost[0]->etd)) . ' hari',
              ])
              ->toArray()
          );
        }
      }
    }

    $carts = $carts->map(fn ($cart) => [
      'id' => $cart['id'],
      'product' => [
        'id' => $cart['product']['id'],
        'name' => $cart['product']['name'],
        'slug' => $cart['product']['slug'],
        'image' => $cart['product']['image'],
        'category' => [
          'id' => $cart['product']['category']['id'],
          'name' => $cart['product']['category']['name'],
          'slug' => $cart['product']['category']['slug'],
        ],
        'brand' => [
          'id' => $cart['product']['brand']['id'],
          'name' => $cart['product']['brand']['name'],
          'slug' => $cart['product']['brand']['slug'],
        ],
        'sex' => $cart['product']['sex'],
        'price' => $cart['product']['price'],
        'stock' => $cart['product']['stock'],
        'weight' => $cart['product']['weight'],
        'sold_count' => $cart['product']['sold_count'] ?? 0,
        'is_wishlist' => $cart['product']['is_wishlist'] ?? false,
      ],
      'quantity' => $cart['quantity'],
    ])->toArray();

    $userAddress = $userAddress
      ? [
        'id' => $userAddress['id'],
        'name' => $userAddress['name'],
        'phone' => $userAddress['phone'],
        'province' => [
          'id' => $userAddress['city']['province']['id'],
          'name' => $userAddress['city']['province']['name'],
        ],
        'city' => [
          'id' => $userAddress['city']['id'],
          'type' => $userAddress['city']['type'],
          'name' => $userAddress['city']['name'],
        ],
        'district' => $userAddress['district'],
        'postal_code' => $userAddress['postal_code'],
        'address' => $userAddress['address'],
      ]
      : null;

    $banks = Bank::all()
      ->map(fn ($bank) => [
        'id' => $bank['id'],
        'name' => $bank['name'],
        'code' => $bank['code'],
        'account_name' => $bank['account_name'],
        'account_number' => $bank['account_number'],
        'logo' => $bank['logo']
      ])->toArray();

    return response()
      ->json([
        'data' => [
          'shipping_address' => $userAddress,
          'carts' => $carts,
          'shipping_methods' => empty($costs) ? null : $costs,
          'payment_methods' => [
            'bank' => $banks
          ],
          'total_quantity' => $totalQuantity,
          'total_weight' => $totalWeight,
          'total_price' => $totalPrice,
        ]
      ])->setStatusCode(200);
  }
}
