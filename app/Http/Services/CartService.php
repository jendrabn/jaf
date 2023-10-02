<?php

namespace App\Http\Services;

use App\Models\Cart;
use App\Models\Shipping;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class CartService
{
  public function getTotals(Collection|array $carts): array
  {
    $totalQuantity = 0;
    $totalWeight = 0;
    $totalPrice = 0;

    foreach ($carts as $cart) {
      $totalQuantity += $cart['quantity'];
      $totalWeight += $cart['quantity'] * $cart['product']['weight'];
      $totalPrice += $cart['quantity'] * $cart['product']['price'];
    }

    return [$totalQuantity, $totalWeight, $totalPrice];
  }

  public function validateProduct(Cart $cart)
  {
    if (!$cart['product']['is_publish']) {

      $cart->delete();

      throw ValidationException::withMessages([
        'product' => 'The product must be published.'
      ]);
    }
  }

  public function validateQuantity(Cart $cart)
  {
    if ($cart['quantity'] > $cart['product']['stock']) {
      throw ValidationException::withMessages([
        'cart' => 'The quantity [ID' . $cart['id'] . '] must not be greater than stock.'
      ]);
    }
  }
}
