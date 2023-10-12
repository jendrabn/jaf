<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\HomePageController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Home Page
Route::get('/home_page', HomePageController::class);

// Product
Route::controller(ProductController::class)->group(function () {
  Route::get('/categories', 'categories');
  Route::get('/brands', 'brands');
  Route::get('/products', 'list');
  Route::get('/products/{id}', 'get');
  Route::get('/products/{id}/similars', 'similars');
});

// Region
Route::controller(RegionController::class)->group(function () {
  Route::get('/region/provinces', 'provinces');
  Route::get('/region/cities/{province}', 'cities');
});

// Auth
Route::controller(AuthController::class)->group(function () {
  Route::post('/auth/register', 'register');
  Route::post('/auth/login', 'login');
  Route::delete('/auth/logout', 'logout')->middleware('auth:sanctum');
  Route::post('/auth/forgot_password', 'sendPasswordResetLink');
  Route::put('/auth/reset_password', 'resetPassword');
});

// Shipping Cost
Route::post('/shipping_costs', [CheckoutController::class, 'shippingCost']);

Route::middleware(['auth:sanctum'])->group(function () {

  // User Account
  Route::controller(UserController::class)->group(function () {
    Route::get('/user', 'get');
    Route::put('/user', 'update');
    Route::put('/user/change_password', 'updatePassword');
  });

  // Checkout
  Route::post('/checkout', [CheckoutController::class, 'checkout']);

  // Order
  Route::controller(OrderController::class)->group(function () {
    Route::get('/orders', 'list');
    Route::post('/orders', 'create');
    Route::get('/orders/{id}', 'get');
    Route::post('/orders/{id}/confirm_payment', 'confirmPayment');
    Route::put('/orders/{id}/confirm_order_delivered', 'confirmDelivered');
  });

  // Wishlist
  Route::controller(WishlistController::class)->group(function () {
    Route::get('/wishlist', 'list');
    Route::post('/wishlist', 'create');
    Route::delete('/wishlist', 'delete');
  });

  // Cart
  Route::controller(CartController::class)->group(function () {
    Route::get('/carts', 'list');
    Route::post('/carts', 'create');
    Route::put('/carts/{id}', 'update');
    Route::delete('/carts', 'delete');
  });
});

// Route::fallback(fn () => abort(404));
