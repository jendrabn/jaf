<?php

// app/Http/Controllers/Api/AuthController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ForgotPasswordRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

    throw_if(
      !$user || !Hash::check($request->password, $user->password),
      new HttpResponseException(
        response()
          ->json(['message' => 'The provided credentials are incorrect.'])
          ->setStatusCode(Response::HTTP_UNAUTHORIZED)
      )
    );

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

  public function sendPasswordResetLink(ForgotPasswordRequest $request): JsonResponse
  {
    $status = Password::sendResetLink($request->only('email'));

    throw_if(
      $status !== Password::RESET_LINK_SENT,
      ValidationException::withMessages(['email' => $status])
    );

    return response()
      ->json(['data' => true])
      ->setStatusCode(Response::HTTP_OK);
  }

  public function resetPassword(ResetPasswordRequest $request): JsonResponse
  {
    $status = Password::reset(
      $request->validated(),
      function (User $user, string $password) {
        $user->forceFill([
          'password' => $password
        ])->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));
      }
    );

    throw_unless(
      $status === Password::PASSWORD_RESET,
      ValidationException::withMessages(['email' => $status])
    );

    return response()
      ->json(['data' => true])
      ->setStatusCode(Response::HTTP_OK);
  }
}
