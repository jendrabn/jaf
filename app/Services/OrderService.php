<?php

// app/Services/OrderService.php

namespace App\Services;

use App\Http\Requests\Api\{ConfirmPaymentRequest, CreateOrderRequest};
use App\Models\{Bank, Cart, Invoice, Order, OrderItem, Payment, Shipping};
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

  public function confirmPayment(ConfirmPaymentRequest $request, int $id): void
  {
    $user = auth()->user();
    $order = $user->orders()->findOrFail($id);

    throw_if(
      $order->status !== Order::STATUS_PENDING_PAYMENT,
      ValidationException::withMessages([
        'order' => 'Order status must be pending payment.'
      ])
    );

    if (now()->isAfter($order->invoice->due_date)) {
      $order->status = Order::STATUS_CANCELLED;
      $order->cancel_reason = 'Order canceled by system.';
      $order->save();

      throw ValidationException::withMessages(
        ['order' => 'Order canceled because payment time has expired.']
      );
    }

    try {
      DB::beginTransaction();

      $order->invoice->payment->bank()->create($request->validated());
      $order->status = Order::STATUS_PENDING;
      $order->save();

      DB::commit();
    } catch (QueryException $e) {
      DB::rollBack();

      throw $e;
    }
  }

  public function confirmDelivered(int $id): void
  {
    $user = auth()->user();
    $order = $user->orders()->findOrFail($id);

    throw_if(
      $order->status !== Order::STATUS_ON_DELIVERY,
      ValidationException::withMessages([
        'order' => 'Order status must be on delivery.'
      ])
    );

    try {
      DB::beginTransaction();

      $order->status = Order::STATUS_COMPLETED;
      $order->shipping->status = Shipping::STATUS_SHIPPED;
      $order->save();
      $order->shipping->save();

      DB::commit();
    } catch (QueryException $e) {
      DB::rollBack();

      throw $e;
    }
  }

  public function createOrder(CreateOrderRequest $request): Order
  {
    $validatedData = $request->validated();
    $carts = Cart::whereIn('id', $validatedData['cart_ids'])->get();
    $user = auth()->user();

    $this->validateBeforeCreateOrder($carts);

    $shippingAddress = $validatedData['shipping_address'];
    $totalWeight = $this->getTotalWeight($carts);

    $shipping = (new RajaOngkirService)->getService(
      $validatedData['shipping_service'],
      $shippingAddress['city_id'],
      $totalWeight,
      $validatedData['shipping_courier']
    );

    throw_if(
      !$shipping,
      ValidationException::withMessages([
        'shipping_service' => 'Layanan pengiriman yang diinginkan tidak tersedia.'
      ])
    );

    $totalPrice = $this->getTotalPrice($carts);
    $shippingCost = $shipping['cost'];
    $totalAmount = $totalPrice + $shippingCost;

    DB::beginTransaction();

    try {
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
        'number' => implode('/', [
          'INV', $order->created_at->format('YYYYMMDD'), $order->id
        ]),
        'amount' => $totalAmount,
        'status' => Invoice::STATUS_UNPAID,
        'due_date' => $order->created_at->addDays(1),
      ]);

      $bank = Bank::findOrFail($validatedData['bank_id']);

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

  public function validateBeforeCreateOrder(Collection $carts): void
  {
    throw_if(
      $carts->isEmpty(),
      ValidationException::withMessages([
        'cart' => 'Cart is empty.'
      ])
    );

    foreach ($carts as $item) {
      $product = $item->product;

      if (!$product->is_publish) {
        $item->delete();

        throw ValidationException::withMessages([
          'product' => 'Product is not available.'
        ]);
      }

      throw_if(
        $item->quantity > $product->stock,
        ValidationException::withMessages([
          'cart' => 'Quantity exceeds available stock.'
        ])
      );
    }

    $totalWeight = $this->getTotalWeight($carts);

    throw_if(
      $totalWeight > Shipping::MAX_WEIGHT,
      ValidationException::withMessages([
        'cart' => 'Total weight must not be more than 25kg.'
      ])
    );
  }

  public function getTotalWeight(Collection $items)
  {
    return $items->reduce(
      fn ($carry, $item) => $carry + ($item->quantity * $item->product->weight)
    );
  }

  public function getTotalPrice(Collection $items): int
  {
    return $items->reduce(
      fn ($carry, $item) => $carry + ($item->quantity * $item->product->price)
    );
  }

  public function getTotalQuantity(Collection $items): int
  {
    return $items->reduce(
      fn ($carry, $item) => $carry + $item->quantity
    );
  }
}
