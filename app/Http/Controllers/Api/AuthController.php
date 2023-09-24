<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
  public function register(RegisterRequest $request): JsonResponse
  {
    $validatedData = $request->validated();
    $validatedData['password'] = Hash::make($validatedData['password']);

    $user = User::create($validatedData);

    $user->assignRole('user');

    return response()->json([
      'data' => [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'phone' => $user->phone,
        'sex' => $user->sex,
        'birth_date' => $user->birth_date,
      ]
    ], 201);
  }
}
