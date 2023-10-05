<?php
// app/Http/Services/ProductService.php
namespace App\Http\Services;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductService
{
  public function getProducts(Request $request, ?int $size = 20)
  {
    $page = $request->get('page', 1);

    $products = Product::published();

    // Filter
    $products->when(
      $request->has('category_id'),
      fn ($q) => $q->where('product_category_id', $request->get('category_id'))
    );

    $products->when(
      $request->has('brand_id'),
      fn ($q) => $q->where('product_brand_id', $request->get('brand_id'))
    );

    $products->when(
      $request->has('sex'),
      fn ($q) => $q->where('sex', $request->get('sex'))
    );

    $products->when(
      $request->has('price_min') && $request->has('price_max'),
      fn ($q) => $q->whereBetween('price', [$request->get('price_min'), $request->get('price_max')])
    );

    // Search
    $products->when(
      $request->has('search'),
      function ($q) use ($request) {
        $search = $request->get('search');

        $q->where('name', 'like', '%' . $search . '%')
          ->orWhereHas('category', fn ($q) => $q->where('name', 'like', '%' . $search . '%'))
          ->orWhereHas('brand', fn ($q) => $q->where('name', 'like', '%' . $search . '%'));
      }
    );

    // Sort
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

  public function getProductSimilars(string $keyword, ?int $size = 5)
  {
    $products = Product::published()
      ->where('name', 'like', '%' . $keyword . '%')->latest('id')->take($size)->get();

    return $products;
  }
}
