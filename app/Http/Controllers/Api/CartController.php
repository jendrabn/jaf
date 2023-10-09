<?php

// app/Http/Controllers/Api/CartController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\{CreateCartRequest, DeleteCartRequest, UpdateCartRequest};
use App\Http\Resources\CartResource;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CartController extends Controller
{
  public function __construct(private CartService $cartService)
  {
  }

  public function list(): JsonResponse
  {
    $user = auth()->user();
    $carts = $user->carts->sortByDesc('id');

    return CartResource::collection($carts)
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function create(CreateCartRequest $request): JsonResponse
  {
    $this->cartService->create($request);

    return response()->json(['data' => true], Response::HTTP_CREATED);
  }

  public function update(UpdateCartRequest $request, int $id): JsonResponse
  {
    $this->cartService->update($request, $id);

    return response()->json(['data' => true], Response::HTTP_OK);
  }

  public function delete(DeleteCartRequest $request): JsonResponse
  {
    $user = auth()->user();
    $user->carts()->whereIn('id', $request->validated('cart_ids'))->delete();

    return response()->json(['data' => true], Response::HTTP_OK);
  }
}
