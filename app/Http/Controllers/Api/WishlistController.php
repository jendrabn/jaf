<?php

// app/Http/Controllers/Api/WishlistController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\{CreateWishlistRequest, DeleteWishlistRequest};
use App\Http\Resources\WishlistResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class WishlistController extends Controller
{
  public function list(): JsonResponse
  {
    $user = auth()->user();
    $wishlist = $user->wishlists()->latest('id')->get();

    return WishlistResource::collection($wishlist)
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function create(CreateWishlistRequest $request): JsonResponse
  {
    $user = auth()->user();
    $user->wishlists()->firstOrCreate($request->validated());

    return response()->json(['data' => true], Response::HTTP_CREATED);
  }

  public function delete(DeleteWishlistRequest $request): JsonResponse
  {
    $user = auth()->user();
    $user->wishlists()->whereIn('id', $request->validated('wishlist_ids'))
      ->delete();

    return response()->json(['data' => true], Response::HTTP_OK);
  }
}
