<?php
// app/Http/Controllers/Api/HomePageController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Http\Resources\ProductResource;
use App\Models\Banner;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HomePageController extends Controller
{
  public function __invoke(): JsonResponse
  {
    $banners = Banner::take(10)->get();
    $products = Product::published()->latest('id')->take(10)->get();

    return response()
      ->json([
        'data' => [
          'banners' => BannerResource::collection($banners),
          'products' => ProductResource::collection($products),
        ]
      ])
      ->setStatusCode(Response::HTTP_OK);
  }
}
