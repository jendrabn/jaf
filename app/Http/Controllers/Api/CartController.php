<?php
// app\Http\Controllers\Api\CartController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateCartRequest;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
  public function create(CreateCartRequest $request)
  {
    $product = Product::where('is_publish', true)
      ->where('id', $request->validated('product_id'))
      ->first();

    if ($product === null) {
      throw ValidationException::withMessages([
        'product' => 'Produk tidak tersedia'
      ]);
    }

    $cart = Cart::where('product_id', $request->validated('product_id'))
      ->first();

    if ($request->validated('quantity') + ($cart['quantity'] ?? 0) > $product['stock']) {
      throw ValidationException::withMessages([
        'cart' => 'Kuantitas melebihi stok yang tersedia.'
      ]);
    }

    if ($cart === null) {
      Cart::create([
        'user_id' => auth()->id(),
        'product_id' => $request->validated('product_id'),
        'quantity' => $request->validated('quantity'),
      ]);
    } else {
      $cart->update([
        'quantity' => $request->validated('quantity') + $cart['quantity'],
      ]);
    }
    return response()->json(['data' => true])->setStatusCode(201);
  }
}
