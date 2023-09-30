<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductBrandResource;
use App\Http\Resources\ProductCategoryResource;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductDetailResource;
use App\Http\Services\ProductService;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
  public function categories(): JsonResponse
  {
    return ProductCategoryResource::collection(ProductCategory::all())
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function brands(): JsonResponse
  {
    return ProductBrandResource::collection(ProductBrand::all())
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function list(Request $request, ProductService $productService): JsonResponse
  {
    return (new ProductCollection($productService->getProducts($request)))
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function get(Request $request, Product $product): JsonResponse
  {
    $product = $product->query()->withCount([
      'orderItems as sold_count' => fn (Builder $q) =>
      $q->select(DB::raw('IFNULL(SUM(quantity), 0)'))
        ->whereHas('order', fn ($q) => $q->where('status', Order::STATUS_COMPLETED))
    ])->first();

    return (new ProductDetailResource($product))
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }
}
