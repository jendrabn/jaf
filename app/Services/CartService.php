<?php

namespace App\Services;

use App\Http\Requests\Api\CreateCartRequest;
use App\Http\Requests\Api\UpdateCartRequest;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class CartService
{
  /**
   * @param CreateCartRequest $request
   * @return Cart
   */
  public function create(CreateCartRequest $request): Cart
  {
    $validatedData = $request->validated();
    $product = Product::findOrFail($validatedData['product_id']);
    $cart = auth()->user()->carts()->firstOrNew(Arr::only($validatedData, 'product_id'));
    $newQuantity = $cart->quantity + $validatedData['quantity'];

    throw_if(
      $newQuantity > $product->stock,
      ValidationException::withMessages([
        'quantity' => 'The quantity must not be greater than stock.'
      ])
    );

    $cart->quantity = $newQuantity;
    $cart->save();

    return $cart;
  }

  /**
   * @param UpdateCartRequest $request
   * @param integer $cartId
   * @return Cart
   */
  public function update(UpdateCartRequest $request, int $cartId): Cart
  {
    $cart = auth()->user()->carts()->findOrFail($cartId);
    $newQuantity = $request->validated('quantity');

    throw_if(
      $newQuantity > $cart->product->stock,
      ValidationException::withMessages([
        'quantity' => 'The quantity must not be greater than stock.'
      ])
    );

    $cart->quantity = $newQuantity;
    $cart->save();

    return $cart;
  }
}
