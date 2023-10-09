<?php

// app/Services/AuthService.php

namespace App\Services;

use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AuthService
{
  public function login(LoginRequest $request): User
  {
    $validatedData = $request->validated();

    $user = User::whereEmail($validatedData['email'])->first();

    throw_if(
      !$user || !Hash::check($validatedData['password'], $user->password),
      new HttpResponseException(
        response()
          ->json(['message' => 'The provided credentials are incorrect.'])
          ->setStatusCode(Response::HTTP_UNAUTHORIZED)
      )
    );

    $user->auth_token = $user->createToken('auth_token')->plainTextToken;

    return $user;
  }

  public function resetPassword(ResetPasswordRequest $request): void
  {
    $status = Password::reset(
      $request->validated(),
      function (User $user, string $password) {
        $user->forceFill(['password' => $password])
          ->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));
      }
    );

    throw_if(
      $status !== Password::PASSWORD_RESET,
      ValidationException::withMessages(['email' => $status])
    );
  }
}
