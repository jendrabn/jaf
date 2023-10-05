<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateWishlistRequest;
use App\Http\Requests\Api\DeleteWishlistRequest;
use App\Http\Resources\WishlistResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WishlistController extends Controller
{
  public function list(): JsonResponse
  {
    $wishlists = $this->wishlists()->latest('id')->get();

    return WishlistResource::collection($wishlists)
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function create(CreateWishlistRequest $request): JsonResponse
  {
    $this->wishlists()->firstOrCreate([
      'product_id' => $request->validated('product_id')
    ]);

    return response()
      ->json(['data' => true])
      ->setStatusCode(Response::HTTP_CREATED);
  }

  public function delete(DeleteWishlistRequest $request): JsonResponse
  {
    $this->wishlists()
      ->whereIn('id', $request->validated('wishlist_ids'))
      ->delete();

    return response()
      ->json(['data' => true])
      ->setStatusCode(Response::HTTP_OK);
  }

  private function wishlists()
  {
    return auth()->user()->wishlists();
  }
}
