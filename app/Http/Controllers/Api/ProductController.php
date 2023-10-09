<?php

// app/Http/Controllers/Api/ProductController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\{
  ProductBrandResource,
  ProductCategoryResource,
  ProductCollection,
  ProductDetailResource
};
use App\Services\ProductService;
use App\Models\{
  Product,
  ProductBrand,
  ProductCategory
};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
  public function __construct(private ProductService $productService)
  {
  }

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

  public function list(Request $request): JsonResponse
  {
    $products = $this->productService->getProducts($request);

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

  public function similars(int $id): JsonResponse
  {
    $product = Product::published()->findOrFail($id);
    $productSimilars = $this->productService->getProductSimilars($product->name);

    return (new ProductCollection($productSimilars))
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }
}
