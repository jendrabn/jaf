<?php

// app/Http/Controllers/Api/AuthController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
  public function register(RegisterRequest $request): JsonResponse
  {
    $user = User::create($request->validated())
      ->assignRole('user');

    return (new UserResource($user))->response()->setStatusCode(Response::HTTP_CREATED);
  }

  public function login(LoginRequest $request): JsonResponse
  {
    $user = User::whereEmail($request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
      return response()
        ->json(['message' => 'The provided credentials are incorrect.'])
        ->setStatusCode(Response::HTTP_UNAUTHORIZED);
    }

    $user->auth_token = $user->createToken('auth_token')->plainTextToken;

    return (new UserResource($user))
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function logout(): JsonResponse
  {
    auth()->user()->tokens()->delete();

    return response()
      ->json(['data' => true])
      ->setStatusCode(Response::HTTP_OK);
  }
}
