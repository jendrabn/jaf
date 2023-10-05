<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Shipping;
use Illuminate\Database\Eloquent\Collection;

class CartService
{
  public function getTotalQuantity(Collection $carts): int
  {
    return $carts->reduce(
      fn ($carry, $cart) => $carry + $cart->quantity
    );
  }

  public function getTotalWeight(Collection $carts): int
  {
    return $carts->reduce(
      fn ($carry, $cart) => $carry + ($cart->quantity * $cart->product->weight)
    );
  }
  public function getTotalPrice(Collection $carts): int
  {
    return $carts->reduce(
      fn ($carry, $cart) => $carry + ($cart->quantity * $cart->product->price)
    );
  }

  public function validateProduct(Cart $cart): bool
  {
    if ($cart->product->is_publish) return true;

    $cart->delete();

    return false;
  }

  public function validateQuantity(Cart $cart): bool
  {
    return $cart->quantity < $cart->product->stock;
  }

  public function validateTotalWeight(int $totalWeight): bool
  {
    return $totalWeight < Shipping::MAX_WEIGHT;
  }
}
