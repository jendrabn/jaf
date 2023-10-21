<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
  /**
   * @param Request $request
   * @param integer $size
   * @return LengthAwarePaginator
   */
  public function getProducts(Request $request, int $size = 20): LengthAwarePaginator
  {
    $page = $request->get('page', 1);

    $products = Product::published();

    $filters = [
      'category_id' => 'product_category_id',
      'brand_id' => 'product_brand_id',
      'sex' => 'sex'
    ];

    foreach ($filters as $key => $col) {
      $products->when($request->has($key), fn ($q) => $q->where($col, $request->get($key)));
    }

    $products->when(
      $request->has('price_min') && $request->has('price_max'),
      fn ($q) => $q->whereBetween('price', [...$request->only('price_min', 'price_max')])
    );

    $products->when($request->has('search'), function ($q) use ($request) {
      $searchTerm = $request->get('search');

      $q->where('name', 'like', "%{$searchTerm}%")
        ->orWhereHas('category', fn ($q) => $q->where('name', 'like', "%{$searchTerm}%"))
        ->orWhereHas('brand', fn ($q) => $q->where('name', 'like', "%{$searchTerm}%"));
    });

    $products->when(
      $request->has('sort_by'),
      function ($q) use ($request) {
        $sorts = [
          'newest' => ['id', 'desc'],
          'oldest' => ['id', 'asc'],
          'sales' => ['sold_count', 'desc'],
          'expensive' => ['price', 'desc'],
          'cheapest' => ['price', 'asc'],
        ];

        $q->orderBy(...$sorts[$request->get('sort_by')] ?? $sorts['newest']);
      },
      fn ($q) => $q->orderBy('id', 'desc')
    );

    $products = $products->paginate(perPage: $size, page: $page);

    return $products;
  }

  /**
   * @param string $keyword
   * @param integer $size
   * @return Collection
   */
  public function getProductSimilars(string $keyword, int $size = 5): Collection
  {
    return Product::published()->where('name', 'like', "%{$keyword}%")->latest('id')->take($size)->get();
  }
}
