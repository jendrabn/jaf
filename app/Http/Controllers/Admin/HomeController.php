<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Banner;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $total_admin = User::role(User::ROLE_ADMIN)->count();
        $total_users = User::role(User::ROLE_USER)->count();
        $total_categories = ProductCategory::count();
        $total_brands = ProductBrand::count();
        $total_products = Product::count();
        $total_orders = Order::count();
        $total_banners = Banner::count();
        $total_payment_banks = Bank::count();
        $orders = Order::latest()->take(5)->get();

        return view(
            'admin.dashboard',
            compact(
                'total_admin',
                'total_users',
                'total_categories',
                'total_brands',
                'total_products',
                'total_orders',
                'total_banners',
                'total_payment_banks',
                'orders',
            )
        );
    }
}
