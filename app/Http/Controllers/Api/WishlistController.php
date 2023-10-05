<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
}
