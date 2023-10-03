<?php
// app/Http/Controllers/Api/HomePageController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Banner;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomePageController extends Controller
{
  public function __invoke(): JsonResponse
  {
    $banners = Banner::orderBy('id')->take(10)->get();
    $products = Product::published()->latest('id')->take(10)->get();

    return response()->json([
      'data' => [
        'banners' => $banners->map(fn ($banner) => [
          'id' => $banner->id,
          'image' => $banner->image,
          'image_alt' => $banner->image_alt,
          'url' => $banner->url,
        ])->toArray(),
        'products' => ProductResource::collection($products)
      ]
    ]);
  }
}
