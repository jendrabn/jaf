<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\{ProfileRequest, UpdatePasswordRequest};
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
  public function get(): JsonResponse
  {
    $user = auth()->user();

    return UserResource::make($user)
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function update(ProfileRequest $request): JsonResponse
  {
    $user = auth()->user();

    $user->update($request->validated());

    return UserResource::make($user)
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function updatePassword(UpdatePasswordRequest $request): JsonResponse
  {
    $user = auth()->user();

    $user->update(['password' => $request->validated('password')]);

    return response()->json(['data' => true], Response::HTTP_OK);
  }
}
