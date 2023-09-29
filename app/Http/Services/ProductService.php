<?php

// app/Http/Services/ProductService.php

namespace App\Http\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductService
{
  public function getProducts(Request $request, ?int $size = 20)
  {
    $page = $request->get('page', 1);

    // Get product with sold count
    $products = Product::query()
      ->where('is_publish', true)
      ->withCount([
        'orderItems as sold_count' => fn (Builder $q) =>
        $q->select(DB::raw('IFNULL(SUM(quantity), 0)'))
          ->whereHas('order', fn ($q) => $q->where('status', Order::STATUS_COMPLETED))
      ]);

    // Filter
    $products->when(
      $request->has('category_id'),
      fn (Builder $q) => $q->where('product_category_id', $request->get('category_id'))
    );
    $products->when(
      $request->has('brand_id'),
      fn (Builder $q) => $q->where('product_brand_id', $request->get('brand_id'))
    );
    $products->when(
      $request->has('sex'),
      fn (Builder $q) => $q->where('sex', $request->get('sex'))
    );
    $products->when(
      $request->has('price_min') && $request->has('price_max'),
      fn (Builder $q) => $q->whereBetween('price', [$request->get('price_min'), $request->get('price_max')])
    );

    // Search
    $products->when($request->has('search'), function (Builder $q) use ($request) {
      $search = $request->get('search');
      $q->where('name', 'like', '%' . $search . '%')
        ->orWhereHas('category', fn ($q) => $q->where('name', 'like', '%' . $search . '%'))
        ->orWhereHas('brand', fn ($q) => $q->where('name', 'like', '%' . $search . '%'));
    });

    // Sorting
    $sorts = [
      'newest' => ['id', 'desc'],
      'oldest' => ['id', 'asc'],
      'sales' => ['sold_count', 'desc'],
      'expensive' => ['price', 'desc'],
      'cheapest' => ['price', 'asc'],
    ];
    $sortBy = $request->get('sort_by', 'newest');
    $products->orderBy(...$sorts[$sortBy] ?? $sorts['newest']);

    $products = $products->paginate(perPage: $size, page: $page);

    return $products;
  }
}
