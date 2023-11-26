<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipping;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
  public function index(Request $request)
  {
    if ($request->ajax()) {
      $model = Order::query()
        ->with(['user', 'items', 'items.product', 'items.product.media', 'invoice', 'shipping'])
        ->select('orders.*');

      $table = DataTables::eloquent($model)
        ->filter(function ($q) use ($request) {
          $q->when(
            $request->filled('status'),
            fn ($q) => $q->where('status', $request->get('status'))
          );
        }, true);

      $table->addColumn('placeholder', '&nbsp;');
      $table->addColumn('actions', '&nbsp;');

      $table->editColumn('actions', function ($row) {
        return sprintf(
          '<a class="btn btn-xs btn-info" href="%s">%s</a>',
          route('admin.orders.show', $row->id),
          __('View')
        );
      });
      $table->editColumn('id', function ($row) {
        return $row->id ? $row->id : '';
      });
      $table->editColumn('user', function ($row) {
        return $row->user
          ? sprintf(
            '<a href="%s" target="_blank">%s</a>',
            route('admin.users.show', [$row->user->id]),
            $row->user->name
          )
          : '';
      });
      $table->editColumn('items', function ($row) {
        return view('admin.orders.items', compact('row'));
      });
      $table->editColumn('amount', function ($row) {
        return $row->invoice ? 'Rp ' . number_format((float) $row->invoice->amount, 0, ',', '.') : '';
      });
      $table->editColumn('shipping', function ($row) {
        return $row->shipping ? strtoupper($row->shipping->courier) . ' ' . $row->shipping->tracking_number  : '';
      });
      $table->editColumn('status', function ($row) {
        $status =  Order::STATUSES[$row->status];

        return sprintf('<span class="badge badge-%s">%s</span>', $status['color'], $status['label']);
      });

      $table->rawColumns(['actions', 'user', 'placeholder', 'status', 'items', 'shipping']);

      return $table->make(true);
    }


    return view('admin.orders.index');
  }

  public function show(Order $order)
  {
    $order->load('user', 'items', 'items.product', 'invoice', 'invoice.payment', 'invoice.payment.bank', 'shipping');

    return view('admin.orders.show', compact('order'));
  }

  public function confirmPayment(Request $request, Order $order)
  {
    $validatedData = $request->validate([
      'action' => ['required', 'string', 'in:accept,reject'],
      'cancel_reason' => ['nullable', 'string', 'min:1', 'max:100']
    ]);

    try {
      DB::transaction(function () use ($validatedData, $order) {
        if ($validatedData['action'] === 'accept') {
          $order->update([
            'status' => Order::STATUS_PROCESSING,
            'confirmed_at' => now()
          ]);
          $order->invoice->update(['status' => Invoice::STATUS_PAID]);
          $order->invoice->payment->update(['status' => Payment::STATUS_RELEASED]);
        }

        if ($validatedData['action'] === 'reject') {
          $order->update([
            'status' => Order::STATUS_CANCELLED,
            'cancel_reason' => $validatedData['cancel_reason']
          ]);
          $order->invoice->update(['status' => Invoice::STATUS_UNPAID]);
          $order->invoice->payment->update(['status' => Payment::STATUS_CANCELLED]);
          $order->items->each(function ($item) {
            $item->product->increment('stock', $item->quantity);
          });
        }
      });
    } catch (QueryException $e) {
      return response()->json([], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    return response()->json([], Response::HTTP_NO_CONTENT);
  }

  public function confirmShipping(Request $request, Order $order)
  {
    $validatedData = $request->validate([
      'tracking_number' => ['required', 'string', 'min:1', 'max:50',]
    ]);

    try {
      DB::transaction(function () use ($order, $validatedData) {
        $order->update(['status' => Order::STATUS_ON_DELIVERY]);
        $order->shipping->update([
          'status' => Shipping::STATUS_PROCESSING,
          'tracking_number' => $validatedData['tracking_number']
        ]);
      });
    } catch (QueryException $e) {
      throw $e;
    }

    return back();
  }
}
