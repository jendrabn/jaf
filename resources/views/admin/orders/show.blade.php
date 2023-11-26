@extends('layouts.admin')

@section('content')
  @php
    $status = App\Models\Order::STATUSES[$order->status];
    $payment = $order->invoice->payment;
    $shipping = $order->shipping;
  @endphp

  <div class="callout callout-{{ $status['color'] }}">
    <h4 class="font-weight-bold mb-0">{{ $status['label'] }}</h4>
    @if ($order->status === App\Models\Order::STATUS_CANCELLED)
      <span class="mt-2">{{ $order->cancel_reason }}</span>
    @endif
  </div>

  <div class="row"
    style="margin-bottom: 10px;">
    <div class="col-lg-12">
      <div class="bs-stepper">
        <div class="bs-stepper-header">
          <div class="step active">
            <button class="step-trigger">
              <span class="bs-stepper-circle">
                <i class="fas fa-shopping-basket"></i>
              </span>
              <span class="bs-stepper-label">
                Order Created
              </span>
            </button>
          </div>
          <div class="line"></div>
          <div
            class="step {{ $order->status === App\Models\Order::STATUS_PROCESSING || $order->invoice->status === App\Models\Invoice::STATUS_PAID ? 'active' : '' }}">
            <button class="step-trigger">
              <span class="bs-stepper-circle"><i class="fas fa-dollar-sign"></i></span>
              <span class="bs-stepper-label">
                Order Paid

              </span>
            </button>
          </div>
          <div class="line"></div>
          <div
            class="step {{ $order->status === App\Models\Order::STATUS_ON_DELIVERY || $order->shipping->status === App\Models\Shipping::STATUS_SHIPPED ? 'active' : '' }}">
            <button class="step-trigger">
              <span class="bs-stepper-circle"><i class="fas fa-truck"></i></span>
              <span class="bs-stepper-label">Order Shipped Out</span>
            </button>
          </div>
          <div class="line"></div>
          <div class="step {{ $order->status === App\Models\Order::STATUS_COMPLETED ? 'active' : '' }}">
            <button class="step-trigger">
              <span class="bs-stepper-circle"><i class="fas fa-check"></i></span>
              <span class="bs-stepper-label">Order Completed</span>
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>

  {{-- Payment --}}
  <div class="card">
    <div class="card-header">
      Payment <span
        class="float-right font-weight-bold font-italic">{{ App\Models\Payment::STATUSES[$payment->status]['label'] }}</span>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-sm-6 mb-3">
          Total Amount: <span class="h5 font-weight-bold">@rupiah($order->invoice->amount)</span>
        </div>
        <div class="col-sm-6 mb-3">
          Due Date: {{ $order->invoice->due_date }}<br>
        </div>
        <div class="col-sm-6 mb-3">
          <p>Transfer From (Customer)</h5>
            @if ($payment->bank)
              <table class="table table-sm">
                <tbody>
                  <tr>
                    <th>Bank Name</th>
                    <td>{{ $payment->bank->name }}</td>
                  </tr>
                  <tr>
                    <th>Account Name</th>
                    <td>{{ $payment->bank->account_name }}</td>
                  </tr>
                  <tr>
                    <th>Account Number</th>
                    <td>{{ $payment->bank->account_number }}</td>
                  </tr>
                </tbody>
              </table>
            @else
            @endif
        </div>
        <div class="col-sm-6 mb-3">
          <p>Transfer To (Shop)</h5>
          <table class="table table-sm">
            <tbody>
              <tr>
                <th>Bank Name</th>
                <td>{{ $payment->info['name'] }}</td>
              </tr>
              <tr>
                <th>Account Name</th>
                <td>{{ $payment->info['account_name'] }}</td>
              </tr>
              <tr>
                <th>Account Number</th>
                <td>{{ $payment->info['account_number'] }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="col-sm-12">
          @if ($order->status === App\Models\Order::STATUS_PENDING)
            <button class="btn btn-primary mr-1"
              id="accept-payment"
              type="button">
              <i class="fas fa-check mr-1"></i>
              Accept
            </button>
            <button class="btn btn-danger"
              id="reject-payment"
              type="button">
              <i class="fas fa-times mr-1"></i>
              Reject
            </button>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Shipping --}}
  <div class="card">
    <div class="card-header">
      Shipping
      <span
        class="float-right font-weight-bold font-italic">{{ App\Models\Shipping::STATUSES[$shipping->status]['label'] }}</span>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-sm-6 mb-3">
          <p>Shipping Address</p>
          <strong>{{ $shipping->address['name'] }}</strong><br>
          {{ $shipping->address['phone'] }}<br>
          {{ $shipping->address['address'] }},
          {{ $shipping->address['city'] }},
          {{ $shipping->address['district'] }},
          {{ $shipping->address['province'] }},
          {{ $shipping->address['postal_code'] }}
          </address>
        </div>
        <div class="col-sm-6 mb-3">
          <p>Shipping Information</p>
          <table class="table table-sm">
            <tr>
              <th>Courier</th>
              <td>{{ strtoupper($shipping->courier) }} - {{ $shipping->courier_name }}</td>
            </tr>
            <tr>
              <th>Courier Service</th>
              <td>{{ $shipping->service }} {{ $shipping->service_name ? ' - ' . $shipping->service_name : '' }}</td>
            </tr>
            <tr>
              <th>Estimation</th>
              <td>{{ $shipping->etd }}</td>
            </tr>
            <tr>
              <th>Weight</th>
              <td>{{ (int) ceil($shipping->weight / 1000) }} kg</td>
            </tr>
            <tr>
              <th>Tracking Number</th>
              <td>{{ $shipping->tracking_number }}</td>
            </tr>
          </table>
        </div>
        <div class="col-sm-12">
          @if ($order->status === App\Models\Order::STATUS_PROCESSING)
            <form id="confirm-shipping"
              action="{{ route('admin.orders.confirm-shipping', [$order->id]) }}"
              method="POST">
              @method('PUT')
              @csrf
              <div class="form-group">
                <label for="tracking_number">Tracking Number</label>
                <input class="form-control"
                  id="tracking_number"
                  name="tracking_number"
                  type="text"
                  required>
              </div>
              <div class="form-group mb-0">
                <button class="btn btn-primary"
                  type="submit">
                  <i class="fas fa-save mr-1"></i>
                  Save
                </button>
              </div>
            </form>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Invoice --}}
  <div class="invoice mb-3 p-3"
    id="invoice-root">

    <div class="row mb-3">
      <div class="col-12">
        <h5>
          Invoice #{{ $order->invoice->number }} -
          <span
            class="font-weight-bold font-italic">{{ App\Models\Invoice::STATUSES[$order->invoice->status]['label'] }}</span>
          <span class="float-right">Order #{{ $order->id }}</span>
        </h5>
      </div>
    </div>

    <div class="row invoice-info">
      <div class="col-sm-4 invoice-col">
        From
        <address>
          <strong>{{ config('shop.address.name') }}</strong><br>
          {{ config('shop.address.phone') }} <br>
          {{ config('shop.address.city_name') }},
          {{ config('shop.address.province_name') }}
        </address>
      </div>

      <div class="col-sm-4 invoice-col">
        To
        <address>
          <strong>{{ $shipping->address['name'] }}</strong><br>
          {{ $shipping->address['phone'] }}<br>
          {{ $shipping->address['address'] }},
          {{ $shipping->address['city'] }},
          {{ $shipping->address['district'] }},
          {{ $shipping->address['province'] }},
          {{ $shipping->address['postal_code'] }}
        </address>
      </div>

      <div class="col-sm-4 invoice-col">
        <b>Date: </b>{{ $order->created_at }}<br>
        <b>Customer: </b><a
          href="{{ route('admin.users.show', [$order->user->id]) }}"target="_blank">{{ $order->user->name }}</a><br>
        <b>Payment Method: </b>{{ strtoupper($payment->method) }} - {{ $payment->info['name'] ?? '' }}<br>
        <b>Shipping: </b>{{ strtoupper($shipping->courier) }} - {{ $shipping->service }}
      </div>
    </div>

    <div class="row">
      <div class="col-12 table-responsive">
        <table class="table-striped table">
          <thead>
            <tr>
              <th>{{ __('#') }}</th>
              <th>{{ __('Product(s)') }}</th>
              <th>{{ __('Price') }}</th>
              <th>{{ __('Quantity') }}</th>
              <th>{{ __('Subtotal') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($order->items as $key => $item)
              <tr>
                <td>{{ ++$key }}</td>
                <td>
                  @if ($item->product?->image)
                    <a class="inline-block no-print mr-1"
                      href="{{ $item->product->image->getUrl() }}"
                      target="_blank">
                      <img src="{{ $item->product->image->getUrl('thumb') }}">
                    </a>
                  @endif
                  <a href="{{ route('admin.products.show', [$item->product_id]) }}"
                    target="_blank">
                    {{ $item->name }}
                  </a>
                </td>
                <td>@rupiah($item->price)</td>
                <td>{{ $item->quantity }}</td>
                <td>@rupiah((int) $item->price * (int) $item->quantity)</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

    </div>

    <div class="row">
      <div class="col-6">
      </div>

      <div class="col-6">
        <div class="table-responsive">
          <table class="table">
            <tr>
              <th style="width:50%">Total Price</th>
              <td>@rupiah($order->total_price)</td>
            </tr>
            <tr>
              <th>Shipping Cost</th>
              <td>@rupiah($order->shipping_cost)</td>
            </tr>
            <tr>
              <th>Total Amount</th>
              <td>@rupiah($order->invoice->amount)</td>
            </tr>
          </table>
        </div>
      </div>
    </div>

    <div class="row no-print">
      <div class="col-12">
        <button class="btn btn-primary"
          id="btn-print-invoice"
          type="button">
          <i class="fas fa-print"></i> Print
        </button>
      </div>
    </div>

  </div>
@endsection

@section('scripts')
  @parent
  <script>
    $(function() {

      $('#accept-payment').on('click', function(e) {
        Swal.fire({
          titleText: 'Confirm Payment',
          text: "You won't be able to revert this!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#007bff',
          cancelButtonColor: '#c82333',
          confirmButtonText: 'Confirm'
        }).then(function(result) {
          if (result.isConfirmed) {
            $.ajax({
              url: "{{ route('admin.orders.confirm-payment', [$order->id]) }}",
              method: 'PUT',
              headers: {
                'x-csrf-token': _token
              },
              data: {
                action: 'accept',
                _method: 'PUT'
              },
              dataType: 'json'
            }).done(function(data, status, xhr) {
              console.log(status)
            }).fail(function(xhr, status, error) {
              let err = JSON.parse(xhr.responseText);
              Swal.fire('Gagal!', 'Error: ' + err.message, 'error');
            });
          }
        });
      });


      $('#reject-payment').on('click', function(e) {
        Swal.fire({
          title: 'Konfirmasi Pembatalan Pesanan',
          // text: "You won't be able to revert this!",
          icon: 'warning',
          showCancelButton: true,
          focusCancel: true,
          confirmButtonColor: '#007bff',
          cancelButtonColor: '#c82333',
          confirmButtonText: 'Confirm',
          input: 'text',
          inputLabel: 'Alasan Pembatalan',
          inputValue: 'Pembayaran ditolak',
          inputValidator: function(value) {
            if (!value) return 'Alasan pembatalan wajib diisi!'
          }
        }).then(function(result) {
          if (result.isConfirmed) {
            $.ajax({
              url: "{{ route('admin.orders.confirm-payment', [$order->id]) }}",
              method: 'PUT',
              headers: {
                'x-csrf-token': _token
              },
              data: {
                action: 'reject',
                cancel_reason: result.value,
                _method: 'PUT'
              },
              dataType: 'json'
            }).done(function(data, status, xhr) {
              if (xhr.status === 204) {
                Swal.fire('Sukses!', 'Pesanan telah dibatalkan!', 'success');

                setTimeout(() => {
                  window.location.reload()
                }, 1000);

              } else {
                Swal.fire('Gagal!', data.message, 'error');
              }
            }).fail(function(xhr, status, error) {
              let err = JSON.parse(xhr.responseText);

              Swal.fire('Gagal!', 'Error: ' + err.message, 'error');
            });
          }
        });
      });

      $('#confirm-shipping').on('submit', function(e) {
        e.preventDefault();

        Swal.fire({
          titleText: 'Tracking Code: ' + $(this).find('[name="tracking_number"]').val(),
          // text: "You won't be able to revert this!",
          icon: 'warning',
          showCancelButton: true,
          focusCancel: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Save'
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.showLoading();

            $(this).off('submit').submit();
          }
        })

      });



      $('#btn-print-invoice').on('click', function() {
        printJS({
          printable: 'invoice-root',
          type: 'html',
          css: "{{ asset('css/printinvoice.css') }}",
          modalMessage: "Silakan print invoice anda",
          documentTitle: "Invoice"
        })
      })

    });
  </script>
@endsection
