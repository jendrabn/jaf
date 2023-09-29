<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductBrandResource;
use App\Http\Resources\ProductCategoryResource;
use App\Http\Resources\ProductCollection;
use App\Http\Services\ProductService;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
  public function getAllCategories(): JsonResponse
  {
    return ProductCategoryResource::collection(ProductCategory::all())
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function getAllBrands(): JsonResponse
  {
    return ProductBrandResource::collection(ProductBrand::all())
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function index(Request $request, ProductService $productService): JsonResponse
  {
    return (new ProductCollection($productService->getProducts($request)))
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }
}
