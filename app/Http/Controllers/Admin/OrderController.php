<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\OrdersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderRequest;
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
    public function index(OrdersDataTable $dataTable)
    {
        return $dataTable->render("admin.orders.index");
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
            return response()->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        toastr('Payment confirmed successfully.', 'success');

        return back();
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

        toastr('Shipping confirmed successfully.', 'success');

        return back();
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully.']);
    }

    public function massDestroy(OrderRequest $request)
    {
        Order::whereIn('id', $request->validated('ids'))->delete();

        return response()->json(['message' => 'Orders deleted successfully.']);
    }
}
