@extends('layouts.admin')

@section('content')
  <div class="row">
    <div class="col-md-3 col-sm-6 col-12">
      <div class="info-box shadow-lg">
        <span class="info-box-icon bg-secondary"><i class="fas fa-users"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Users</span>
          <span class="info-box-number">{{ $total_users }}</span>
        </div>
      </div>
    </div>

    <div class="col-md-3 col-sm-6 col-12">
      <div class="info-box shadow-lg">
        <span class="info-box-icon bg-secondary"><i class="fas fa-users-cog"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Admin</span>
          <span class="info-box-number">{{ $total_admin }}</span>
        </div>
      </div>
    </div>

    <div class="col-md-3 col-sm-6 col-12">
      <div class="info-box shadow-lg">
        <span class="info-box-icon bg-secondary"><i class="fas fa-folder"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Categories</span>
          <span class="info-box-number">{{ $total_categories }}</span>
        </div>
      </div>
    </div>

    <div class="col-md-3 col-sm-6 col-12">
      <div class="info-box shadow-lg">
        <span class="info-box-icon bg-secondary"><i class="fas fa-folder"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Brands</span>
          <span class="info-box-number">{{ $total_brands }}</span>
        </div>
      </div>
    </div>

    <div class="col-md-3 col-sm-6 col-12">
      <div class="info-box shadow-lg">
        <span class="info-box-icon bg-secondary"><i class="fas fa-folder"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Products</span>
          <span class="info-box-number">{{ $total_products }}</span>
        </div>
      </div>
    </div>

    <div class="col-md-3 col-sm-6 col-12">
      <div class="info-box shadow-lg">
        <span class="info-box-icon bg-secondary"><i class="fas fa-shopping-basket"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Orders</span>
          <span class="info-box-number">{{ $total_orders }}</span>
        </div>
      </div>
    </div>

    <div class="col-md-3 col-sm-6 col-12">
      <div class="info-box shadow-lg">
        <span class="info-box-icon bg-secondary"><i class="fas fa-image"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Banners</span>
          <span class="info-box-number">{{ $total_banners }}</span>
        </div>
      </div>
    </div>

    <div class="col-md-3 col-sm-6 col-12">
      <div class="info-box shadow-lg">
        <span class="info-box-icon bg-secondary"><i class="fas fa-dollar-sign"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Payment Banks</span>
          <span class="info-box-number">{{ $total_payment_banks }}</span>
        </div>
      </div>
    </div>

  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header border-transparent">
          <h3 class="card-title">Latest Orders</h3>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm m-0">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>User</th>
                  <th>Amount</th>
                  <th>Shipping</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @foreach ($orders as $order)
                  <tr>
                    <td>{{ $order->id }}</td>
                    <td>
                      <a href="{{ route('admin.users.show', $order->user_id) }}"target="_blank">
                        {{ $order->user->name }}
                      </a>
                    </td>
                    <td>@rupiah($order->invoice->amount)</td>
                    <td>
                      {{ strtoupper($order->shipping?->courier) . ' ' . $order->shipping?->tracking_number }}
                    </td>
                    <td>{{ $order->created_at }}</td>
                    <td>
                      @php
                        $status = App\Models\Order::STATUSES[$order->status];
                      @endphp
                      <span class="badge badge-{{ $status['color'] }}">
                        {{ $status['label'] }}
                      </span>
                    </td>
                    <td>
                      <a class="btn btn-xs btn-info"
                        href="{{ route('admin.orders.show', [$order->id]) }}">{{ __('View') }}
                      </a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <!-- /.table-responsive -->
        </div>
        <!-- /.card-body -->
        <div class="card-footer clearfix">
          <a class="btn btn-sm btn-secondary float-right"
            href="{{ route('admin.orders.index') }}">View All Orders</a>
        </div>
        <!-- /.card-footer -->
      </div>
    </div>
  </div>
@endsection
