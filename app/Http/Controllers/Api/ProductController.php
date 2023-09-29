<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductBrandResource;
use App\Http\Resources\ProductCategoryResource;
use App\Http\Resources\ProductCollection;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

  public function index(Request $request)
  {
    $size = 20;
    $page = $request->input('page') ?? 1;

    if (!$request->has('sort_by') || !in_array($request->get('sort_by'), ["newest", "oldest", "sales", "expensive", "cheapest"])) {
      $request->merge(['sort_by' => 'newest']);
    }

    $products = Product::where('is_publish', true)
      ->withCount(['orderItems as sold_count' => function ($q) {
        $q->select(DB::raw('IFNULL(SUM(quantity), 0)'))
          ->join('orders', 'order_items.order_id', '=', 'orders.id')
          ->where('orders.status', Order::STATUS_COMPLETED);
      }]);

    // filter
    $products->when($request->has('category_id'), function (Builder $q) use ($request) {
      $q->where('product_category_id', $request->get('category_id'));
    });

    $products->when($request->has('brand_id'), function (Builder $q) use ($request) {
      $q->where('product_brand_id', $request->get('brand_id'));
    });

    $products->when($request->has('sex'), function (Builder $q) use ($request) {
      $q->where('sex', $request->get('sex'));
    });

    $products->when($request->has('price_min') && $request->has('price_max'), function (Builder $q) use ($request) {
      $q->whereBetween('price', [$request->get('price_min'), $request->get('price_max')]);
    });

    // Search
    $products->when($request->has('search'), function (Builder $q) use ($request) {
      $q->where('name', 'like', '%' . $request->get('search') . '%')
        ->orWhereHas('category', function ($q) use ($request) {
          $q->where('name', 'like', '%' . $request->get('search') . '%');
        })
        ->orWhereHas('brand', function ($q) use ($request) {
          $q->where('name', 'like', '%' . $request->get('search') . '%');
        });
    });

    $products->when($request->has('sort_by'), function ($q) use ($request) {
      if ($request->get('sort_by') === 'newest') {
        $q->orderBy('id', 'desc');
      } elseif ($request->get('sort_by') === 'oldest') {
        $q->orderBy('id', 'asc');
      } elseif ($request->get('sort_by') === 'sales') {
        $q->orderBy('sold_count', 'desc');
      } else if ($request->get('sort_by') === 'expensive') {
        $q->orderBy('price', 'desc');
      } elseif ($request->get('sort_by') === 'cheapest') {
        $q->orderBy('price', 'asc');
      }
    });

    $products = $products->paginate(perPage: $size, page: $page);

    return (new ProductCollection($products))
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }
}
