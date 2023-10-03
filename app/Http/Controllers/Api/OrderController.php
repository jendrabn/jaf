<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderCollection;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
  public function list(Request $request): JsonResponse
  {
    $size = 10;
    $page = $request->get('page', 1);
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

    $sortBy = $request->get('sort_by', 'newest');
    $orders->orderBy(...$sorts[$sortBy] ?? $sorts['newest']);

    $orders = $orders->paginate(perPage: $size, page: $page);

    return (new OrderCollection($orders))
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }
}
