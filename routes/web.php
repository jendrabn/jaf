<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BankController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductBrandController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UsersController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([]);
});

// Route::redirect('/', '/auth/login');

Route::get('/home', function () {
    if (session('status')) {
        return redirect()->route('admin.home')->with('status', session('status'));
    }

    return redirect()->route('admin.home');
});

// Auth
Route::middleware('guest')->prefix('auth')->name('auth.')->group(function () {
    Route::get('login', [AuthController::class, 'login'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
    Route::get('forgot_password', [AuthController::class, 'forgotPassword'])->name('forgot_password');
    Route::post('forgot_password', [AuthController::class, 'sendResetPasswordLink'])->name('forgot_password.post');
    Route::get('reset_password', [AuthController::class, 'resetPassword'])->name('reset_password');
    Route::put('reset_password', [AuthController::class, 'resetPassword'])->name('reset_password.put');
});
Route::middleware(['auth', 'role:admin|user'])->get('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Users
    Route::delete('users/destroy', [UsersController::class, 'massDestroy'])->name('users.massDestroy');
    Route::resource('users', UsersController::class);

    // Order
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::put('orders/{order}/confirm_shipping', [OrderController::class, 'confirmShipping'])->name('orders.confirm-shipping');
    Route::put('orders/{order}/confirm_payment', [OrderController::class, 'confirmPayment'])->name('orders.confirm-payment');

    // Product Category
    Route::delete('product-categories/destroy', [ProductCategoryController::class, 'massDestroy'])->name('product-categories.massDestroy');
    Route::resource('product-categories', ProductCategoryController::class, ['except' => ['show']]);

    // Product Brand
    Route::delete('product-brands/destroy', [ProductBrandController::class, 'massDestroy'])->name('product-brands.massDestroy');
    Route::resource('product-brands', ProductBrandController::class, ['except' => ['show']]);

    // Bank
    Route::delete('banks/destroy', [BankController::class, 'massDestroy'])->name('banks.massDestroy');
    Route::post('banks/media', [BankController::class, 'storeMedia'])->name('banks.storeMedia');
    Route::post('banks/ckmedia', [BankController::class, 'storeCKEditorImages'])->name('banks.storeCKEditorImages');
    Route::resource('banks', BankController::class);

    // Banner
    Route::delete('banners/destroy', [BannerController::class, 'massDestroy'])->name('banners.massDestroy');
    Route::post('banners/media', [BannerController::class, 'storeMedia'])->name('banners.storeMedia');
    Route::post('banners/ckmedia', [BannerController::class, 'storeCKEditorImages'])->name('banners.storeCKEditorImages');
    Route::resource('banners', BannerController::class, ['except' => ['show']]);

    // Product
    Route::delete('products/destroy', [ProductController::class, 'massDestroy'])->name('products.massDestroy');
    Route::post('products/media', [ProductController::class, 'storeMedia'])->name('products.storeMedia');
    Route::post('products/ckmedia', [ProductController::class, 'storeCKEditorImages'])->name('products.storeCKEditorImages');
    Route::resource('products', ProductController::class);
});
