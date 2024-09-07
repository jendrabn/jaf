@extends('layouts.admin', ['title' => 'Dashboard'])

@section('content')
    <div class="row">
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-lg">
                <span class="info-box-icon bg-secondary"><i class="fa-solid fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Users</span>
                    <span class="info-box-number">{{ $total_users }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-lg">
                <span class="info-box-icon bg-secondary"><i class="fa-solid fa-users-cog"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Admin</span>
                    <span class="info-box-number">{{ $total_admin }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-lg">
                <span class="info-box-icon bg-secondary"><i class="fa-solid fa-folder"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Categories</span>
                    <span class="info-box-number">{{ $total_categories }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-lg">
                <span class="info-box-icon bg-secondary"><i class="fa-solid fa-folder"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Brands</span>
                    <span class="info-box-number">{{ $total_brands }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-lg">
                <span class="info-box-icon bg-secondary"><i class="fa-solid fa-folder"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Products</span>
                    <span class="info-box-number">{{ $total_products }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-lg">
                <span class="info-box-icon bg-secondary"><i class="fa-solid fa-shopping-basket"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Orders</span>
                    <span class="info-box-number">{{ $total_orders }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-lg">
                <span class="info-box-icon bg-secondary"><i class="fa-solid fa-image"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Banners</span>
                    <span class="info-box-number">{{ $total_banners }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-lg">
                <span class="info-box-icon bg-secondary"><i class="fa-solid fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Payment Banks</span>
                    <span class="info-box-number">{{ $total_payment_banks }}</span>
                </div>
            </div>
        </div>
    </div>
@endsection
