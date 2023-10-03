<?php
// app\Http\Controllers\Api\ProductController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductBrandResource;
use App\Http\Resources\ProductCategoryResource;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductDetailResource;
use App\Http\Services\ProductService;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

  public function get(Product $product): JsonResponse
  {
    throw_if(
      !$product->is_publish,
      new HttpResponseException(response()
        ->json(['message' => 'Not found'])
        ->setStatusCode(Response::HTTP_NOT_FOUND))
    );

    return (new ProductDetailResource($product->first()))
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function similars(Product $product): JsonResponse
  {
    $products = Product::published()
      ->where('name', 'like', '%' . $product->name . '%')
      ->latest('id')
      ->take(5)
      ->get();

    return (new ProductCollection($products))
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }
}
