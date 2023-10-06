<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class CartService
{
  public function addToCart(int $productId, int $quantity, User $user): Cart
  {
    $product = Product::published()->find($productId);

    if (!$product)
      throw ValidationException::withMessages([
        'product' => 'Produk tidak tersedia.'
      ]);

    $cart = $user->carts()->firstOrNew(['product_id' => $productId]);
    $newQuantity = $cart->quantity + $quantity;

    if ($newQuantity > $product->stock)
      throw ValidationException::withMessages([
        'cart' => 'Kuantitas melebihi stok yang tersedia.'
      ]);

    $cart->quantity = $newQuantity;
    $cart->save();

    return $cart;
  }

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
