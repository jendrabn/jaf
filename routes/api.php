<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
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
// User Account
Route::get('/user', [UserController::class, 'getUser'])->middleware('auth:sanctum');
Route::put('/user', [UserController::class, 'updateProfile'])->middleware('auth:sanctum');
Route::put('/user/change_password', [UserController::class, 'updatePassword'])->middleware('auth:sanctum');

// Region
Route::get('/region/provinces', [RegionController::class, 'getAllProvinces']);
Route::get('/region/cities/{province}', [RegionController::class, 'getCitiesByProvinceId']);

// Product
Route::get('/categories', [ProductController::class, 'getAllCategories']);
Route::get('/brands', [ProductController::class, 'getAllBrands']);

// Auth
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::delete('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/auth/forgot_password', [AuthController::class, 'sendPasswordResetLink']);
Route::put('/auth/reset_password', [AuthController::class, 'resetPassword']);
