<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateOrderRequest;
use App\Http\Resources\OrderCollection;
use App\Http\Services\CartService;
use App\Http\Services\OrderService;
use App\Http\Services\RajaOngkirService;
use App\Models\Bank;
use App\Models\Cart;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Shipping;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use function PHPUnit\Framework\throwException;

class OrderController extends Controller
{
  public function list(Request $request, OrderService $orderService): JsonResponse
  {
    return (new OrderCollection($orderService->getOrders($request)))
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function create(CreateOrderRequest $request, CartService $cartService, RajaOngkirService $rajaOngkirService)
  {
    $validatedData = $request->validated();
    $carts = Cart::whereIn('id', $validatedData['cart_ids'])->get();
    $user = auth()->user();
    $bank = Bank::where('id', $validatedData['bank_id'])->firstOrFail();

    $carts->each(function ($cart) use ($cartService) {
      $cartService->validateProduct($cart);
      $cartService->validateQuantity($cart);
    });

    list($totalQuantity, $totalWeight, $totalPrice) = $cartService->getTotals($carts);

    throw_if(
      $totalWeight > Shipping::MAX_WEIGHT,
      ValidationException::withMessages([
        'cart' => 'The total weight must not be greater than 25kg.'
      ])
    );

    $shippingCosts = $rajaOngkirService->getCosts(
      $validatedData['shipping_address']['city_id'],
      $totalWeight,
      [$validatedData['shipping_courier']]
    );

    $shipping = collect($shippingCosts)
      ->firstWhere('service', $validatedData['shipping_service']);

    throw_if(
      !$shipping,
      ValidationException::withMessages(
        ['shipping_service' => 'Layanan kurir tidak tersedia!']
      )
    );

    $totalAmount = $shipping['cost'] + $totalPrice;

    try {
      DB::beginTransaction();

      $order = Order::create([
        'user_id' => $user['id'],
        'total_price' => $totalPrice,
        'shipping_cost' => $shipping['cost'],
        'status' => Order::STATUS_PENDING_PAYMENT,
        'notes' => $validatedData['notes'],
      ]);


      foreach ($carts as $cart) {
        $product = $cart['product'];

        OrderItem::create([
          'order_id' => $order['id'],
          'product_id' => $product->id,
          'name' => $product->name,
          'weight' => $product->weight,
          'price' => $product->price,
          'quantity' => $cart['quantity'],
        ]);

        $product->decrement('stock', $cart['quantity']);
        $cart->delete();
      }

      $invoice = Invoice::create([
        'order_id' => $order['id'],
        'number' => sprintf('INV/%s/%s', $order['created_at']->format('YYYYMMDD'), $order['id']),
        'amount' => $totalAmount,
        'status' => Invoice::STATUS_UNPAID,
        'due_date' => $order['created_at']->addDays(1),
      ]);

      $payment = Payment::create([
        'invoice_id' => $invoice['id'],
        'method' => $validatedData['payment_method'],
        'info' => json_encode([
          'name' => $bank['name'],
          'code' => $bank['code'],
          'account_name' => $bank['account_name'],
          'account_number' => $bank['account_number']
        ]),
        'amount' => $totalAmount,
        'status' => Payment::STATUS_PENDING
      ]);

      $userAddress = UserAddress::create(
        array_merge(['user_id' => $user['id'], ...$validatedData['shipping_address']])
      );

      $shipping =  Shipping::create([
        'order_id' => $order['id'],
        'address' => json_encode([
          'name' =>  $userAddress['name'],
          'phone' =>  $userAddress['phone'],
          'province' => $userAddress['city']['province']['name'],
          'city' => $userAddress['city']['name'],
          'district' =>  $userAddress['district'],
          'postal_code' =>  $userAddress['postal_code'],
          'address' =>  $userAddress['address']
        ]),
        'courier' => $shipping['courier'],
        'courier_name' => $shipping['courier_name'],
        'service' => $shipping['service'],
        'service_name' => $shipping['service_name'],
        'etd' => $shipping['etd'],
        'weight' => $totalWeight,
        'status' => Shipping::STATUS_PENDING
      ]);

      DB::commit();
    } catch (\Exception $e) {
      DB::rollBack();

      throw new InternalErrorException($e->getMessage());
    }

    return response()
      ->json([
        'data' => [
          'id' => $order['id'],
          'total_amount' => $totalAmount,
          'payment_method' => $payment['method'],
          'payment_info' => json_decode($payment['info'], true),
          'payment_due_date' => $invoice['due_date']->toISOString(),
          'created_at' => $order['created_at']
        ]
      ])->setStatusCode(Response::HTTP_CREATED);
  }
}
