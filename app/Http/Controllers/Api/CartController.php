<?php
// app\Http\Controllers\Api\CartController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateCartRequest;
use App\Http\Requests\Api\DeleteCartRequest;
use App\Http\Requests\Api\UpdateCartRequest;
use App\Http\Resources\CartResource;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class CartController extends Controller
{
  public function list(): JsonResponse
  {
    $carts = auth()->user()->carts->sortByDesc('id');

    return CartResource::collection($carts)
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function create(CreateCartRequest $request, CartService $cartService): JsonResponse
  {
    $validatedData = $request->validated();
    $cartService->create($validatedData['product_id'], $validatedData['quantity']);

    return response()->json(['data' => true], Response::HTTP_CREATED);
  }

  public function update(UpdateCartRequest $request, int $id)
  {
    $user = auth()->user();
    $cart = $user->carts()->findOrFail($id);
    $newQuantity = $cart->quantity + $request->validated('quantity');

    if ($newQuantity > $cart->product->stock) {
      throw ValidationException::withMessages(['cart' => 'Kuantitas melebihi stok yang tersedia']);
    }

    $cart->quantity = $newQuantity;
    $cart->save();

    return response()->json(['data' => true], Response::HTTP_OK);
  }

  public function delete(DeleteCartRequest $request): JsonResponse
  {
    $user = auth()->user();
    $user->carts()->whereIn('id', $request->validated('cart_ids'))->delete();

    return response()->json(['data' => true], Response::HTTP_OK);
  }
}
