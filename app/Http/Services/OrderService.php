<?php

namespace App\Http\Services;

use App\Models\Order;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class OrderService
{

  public function getOrders(Request $request, int $size = 10)
  {
    $page = $request->get('page', 1);
    $sortBy = $request->get('sort_by', 'newest');

    $orders = Order::with(['items', 'items.product', 'invoice'])
      ->where('user_id', auth()->id());

    $orders->when(
      $request->has('status'),
      fn (Builder $builder) => $builder->where('status', $request->get('status'))
    );

    $sorts = [
      'newest' => ['id', 'desc'],
      'oldest' => ['id', 'asc']
    ];

    $orders->orderBy(...$sorts[$sortBy] ?? $sorts['newest']);

    $orders = $orders->paginate(perPage: $size, page: $page);

    return $orders;
  }
}
