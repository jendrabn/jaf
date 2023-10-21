<?php

namespace App\Services;

use App\Http\Requests\Api\{ConfirmPaymentRequest, CreateOrderRequest};
use App\Models\{Bank, Cart, Invoice, Order, OrderItem, Payment, Shipping};
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
  /**
   * @param Request $request
   * @param integer $size
   * @return LengthAwarePaginator
   */
  public function getOrders(Request $request, int $size = 10): LengthAwarePaginator
  {
    $page = $request->get('page', 1);

    $orders = auth()->user()->orders();

    $orders->when($request->has('status'), fn ($q) => $q->where('status', $request->get('status')));

    $orders->when(
      $request->has('sort_by'),
      function ($q) use ($request) {
        $sorts = [
          'newest' => ['id', 'desc'],
          'oldest' => ['id', 'asc']
        ];

        $q->orderBy(...$sorts[$request->get('sort_by')] ?? $sorts['newest']);
      },
      fn ($q) => $q->orderBy('id', 'desc')
    );

    $orders = $orders->paginate(perPage: $size, page: $page);

    return $orders;
  }

  /**
   * @param ConfirmPaymentRequest $request
   * @param integer $orderId
   * @return Order
   */
  public function confirmPayment(ConfirmPaymentRequest $request, int $orderId): Order
  {
    $order = auth()->user()->orders()->findOrFail($orderId);

    throw_if(
      $order->status !== Order::STATUS_PENDING_PAYMENT,
      ValidationException::withMessages([
        'order_id' => 'Order status must be pending payment.'
      ])
    );

    if (now()->isAfter($order->invoice->due_date)) {
      $order->status = Order::STATUS_CANCELLED;
      $order->cancel_reason = 'Order canceled by system.';
      $order->save();

      throw ValidationException::withMessages([
        'order_id' => 'Order canceled, payment time has expired.'
      ]);
    }

    try {
      DB::transaction(function () use ($order, $request) {
        $order->invoice->payment->bank()->create($request->validated());
        $order->status = Order::STATUS_PENDING;
        $order->save();
      });
    } catch (QueryException $e) {
      throw $e;
    }

    return $order;
  }

  /**
   * @param integer $orderId
   * @return Order
   */
  public function confirmDelivered(int $orderId): Order
  {
    $order = auth()->user()->orders()->findOrFail($orderId);

    throw_if(
      $order->status !== Order::STATUS_ON_DELIVERY,
      ValidationException::withMessages([
        'order_id' => 'Order status must be on delivery.'
      ])
    );

    try {
      DB::transaction(function () use ($order) {
        $order->status = Order::STATUS_COMPLETED;
        $order->shipping->status = Shipping::STATUS_SHIPPED;
        $order->save();
        $order->shipping->save();
      });
    } catch (QueryException $e) {
      throw $e;
    }

    return $order;
  }

  /**
   * @param CreateOrderRequest $request
   * @return Order
   */
  public function createOrder(CreateOrderRequest $request): Order
  {
    $validatedData = $request->validated();
    $carts = Cart::whereIn('id', $validatedData['cart_ids'])->get();
    $bank = Bank::findOrFail($validatedData['bank_id']);

    $this->validateBeforeCreateOrder($carts);

    $shippingAddress = $validatedData['shipping_address'];
    $totalWeight = $this->totalWeight($carts);

    $shipping = (new RajaOngkirService)->getService(
      $validatedData['shipping_service'],
      $shippingAddress['city_id'],
      $totalWeight,
      $validatedData['shipping_courier']
    );

    throw_if(
      !$shipping,
      ValidationException::withMessages([
        'shipping_service' => 'Shipping service is not available.'
      ])
    );

    $totalPrice = $this->totalPrice($carts);
    $shippingCost = $shipping['cost'];
    $totalAmount = $totalPrice + $shippingCost;

    DB::beginTransaction();
    try {
      $user = auth()->user();

      $order = Order::create([
        'user_id' => $user->id,
        'total_price' => $totalPrice,
        'shipping_cost' => $shippingCost,
        'status' => Order::STATUS_PENDING_PAYMENT,
        'notes' => $validatedData['notes'],
      ]);

      foreach ($carts as $cart) {
        $product = $cart->product;

        OrderItem::create([
          'order_id' => $order->id,
          'product_id' => $product->id,
          'name' => $product->name,
          'weight' => $product->weight,
          'price' => $product->price,
          'quantity' => $cart->quantity,
        ]);

        $product->decrement('stock', $cart->quantity);

        $cart->delete();
      }

      $invoice = Invoice::create([
        'order_id' => $order->id,
        'number' => implode('/', ['INV', $order->created_at->format('YYYYMMDD'), $order->id]),
        'amount' => $totalAmount,
        'status' => Invoice::STATUS_UNPAID,
        'due_date' => $order->created_at->addDays(1),
      ]);

      Payment::create([
        'invoice_id' => $invoice->id,
        'method' => $validatedData['payment_method'],
        'info' => [
          'name' => $bank->name,
          'code' => $bank->code,
          'account_name' => $bank->account_name,
          'account_number' => $bank->account_number
        ],
        'amount' => $totalAmount,
        'status' => Payment::STATUS_PENDING
      ]);

      $userAddress = $user->address()->updateOrCreate($shippingAddress);

      Shipping::create([
        'order_id' => $order->id,
        'address' => [
          'name' =>  $userAddress->name,
          'phone' =>  $userAddress->phone,
          'province' => $userAddress->city->province->name,
          'city' => $userAddress->city->name,
          'district' =>  $userAddress->district,
          'postal_code' =>  $userAddress->postal_code,
          'address' =>  $userAddress->address
        ],
        'courier' => $shipping['courier'],
        'courier_name' => $shipping['courier_name'],
        'service' => $shipping['service'],
        'service_name' => $shipping['service_name'],
        'etd' => $shipping['etd'],
        'weight' => $totalWeight,
        'status' => Shipping::STATUS_PENDING,
      ]);

      DB::commit();
    } catch (QueryException $e) {
      DB::rollBack();

      throw $e;
    }

    return $order;
  }

  /**
   * @param Collection $carts
   * @return void
   */
  public function validateBeforeCreateOrder(Collection $carts): void
  {
    throw_if(
      $carts->isEmpty(),
      ValidationException::withMessages([
        'cart_ids' => 'The carts must not be empty.'
      ])
    );

    foreach ($carts as $cart) {
      throw_if(
        !$cart->product->is_publish,
        ValidationException::withMessages([
          'cart_ids' => 'The product must be published.'
        ])
      );

      throw_if(
        $cart->quantity > $cart->product->stock,
        ValidationException::withMessages([
          'cart_ids' => 'The quantity must not be greater than stock.'
        ])
      );
    }

    throw_if(
      $this->totalWeight($carts) > Shipping::MAX_WEIGHT,
      ValidationException::withMessages([
        'cart_ids' => 'The total weight must not be greater than 25kg.'
      ])
    );
  }

  /**
   * @param Collection $items
   * @return integer
   */
  public function totalWeight(Collection $items): int
  {
    return $items->reduce(fn ($carry, $item) => $carry + ($item->quantity * $item->product->weight));
  }

  /**
   * @param Collection $items
   * @return integer
   */
  public function totalPrice(Collection $items): int
  {
    return $items->reduce(fn ($carry, $item) => $carry + ($item->quantity * $item->product->price));
  }

  /**
   * @param Collection $items
   * @return integer
   */
  public function totalQuantity(Collection $items): int
  {
    return $items->reduce(fn ($carry, $item) => $carry + $item->quantity);
  }
}
