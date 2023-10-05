<?php
// app\Http\Controllers\Api\ProductController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductBrandResource;
use App\Http\Resources\ProductCategoryResource;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductDetailResource;
use App\Services\ProductService;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
  public function categories(): JsonResponse
  {
    $categories = ProductCategory::all();

    return ProductCategoryResource::collection($categories)
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function brands(): JsonResponse
  {
    $brands = ProductBrand::all();

    return ProductBrandResource::collection($brands)
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function list(Request $request, ProductService $productService): JsonResponse
  {
    $products = $productService->getProducts($request);

    return (new ProductCollection($products))
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function get(int $id): JsonResponse
  {
    $product = Product::published()->findOrFail($id);

    return (new ProductDetailResource($product))
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function similars(int $id, ProductService $productService): JsonResponse
  {
    $product = Product::published()->findOrFail($id);
    $productSimilars = $productService->getProductSimilars($product->name);

    return (new ProductCollection($productSimilars))
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }
}
