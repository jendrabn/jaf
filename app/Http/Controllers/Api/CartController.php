<?php
// app\Http\Controllers\Api\CartController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateCartRequest;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CartController extends Controller
{
  public function create(CreateCartRequest $request, CartService $cartService): JsonResponse
  {
    $validatedData = $request->validated();
    $user = auth()->user();

    $cartService->addToCart(
      $validatedData['product_id'],
      $validatedData['quantity'],
      $user
    );

    return response()->json(['data' => true], Response::HTTP_CREATED);
  }
}
