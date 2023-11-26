<?php

use App\Http\Controllers\Admin\BankController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductBrandController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Auth\ChangePasswordController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::redirect('/', '/login');
Route::get('/home', function () {
  if (session('status')) {
    return redirect()->route('admin.home')->with('status', session('status'));
  }

  return redirect()->route('admin.home');
});

Auth::routes(['register' => false]);

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
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

Route::middleware(['auth'])->prefix('profile')->name('profile.')->controller(ChangePasswordController::class)->group(function () {
  // Change password
  Route::get('password', 'edit')->name('password.edit');
  Route::post('password', 'update')->name('password.update');
  Route::post('profile', 'updateProfile')->name('password.updateProfile');
  Route::post('profile/destroy', 'destroy')->name('password.destroyProfile');
});
