<?php

// app/Services/CartService.php

namespace App\Services;

use App\Http\Requests\Api\CreateCartRequest;
use App\Http\Requests\Api\UpdateCartRequest;
use App\Models\Product;
use Illuminate\Validation\ValidationException;

class CartService
{
  public function create(CreateCartRequest $request): void
  {
    $validatedData = $request->validated();
    $product = Product::findOrFail($validatedData['product_id']);
    $user = auth()->user();
    $cart = $user->carts()->firstOrNew(['product_id' => $validatedData['product_id']]);
    $newQuantity = $cart->quantity + $validatedData['quantity'];

    throw_if(
      $newQuantity > $product->stock,
      ValidationException::withMessages([
        'cart' => 'Kuantitas melebihi stok yang tersedia.'
      ])
    );

    $cart->quantity = $newQuantity;
    $cart->save();
  }

  public function update(UpdateCartRequest $request, int $cartId): void
  {
    $user = auth()->user();
    $cart = $user->carts()->findOrFail($cartId);
    $newQuantity = $cart->quantity + $request->validated('quantity');

    throw_if(
      $newQuantity > $cart->product->stock,
      ValidationException::withMessages([
        'cart' => 'Kuantitas melebihi stok yang tersedia'
      ])
    );

    $cart->quantity = $newQuantity;
    $cart->save();
  }
}
