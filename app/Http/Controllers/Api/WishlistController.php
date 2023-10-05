<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateWishlistRequest;
use App\Http\Resources\WishlistResource;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WishlistController extends Controller
{
  public function list(): JsonResponse
  {
    $wishlists = Wishlist::latest('id')->get();

    return WishlistResource::collection($wishlists)
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function create(CreateWishlistRequest $request): JsonResponse
  {
    Wishlist::firstOrCreate([
      'user_id' => auth()->id(),
      'product_id' => $request->validated('product_id')
    ]);

    return response()
      ->json(['data' => true])
      ->setStatusCode(Response::HTTP_CREATED);
  }
}
