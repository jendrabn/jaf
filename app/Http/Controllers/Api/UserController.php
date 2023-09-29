<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProfileRequest;
use App\Http\Requests\Api\UpdatePasswordRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
  public function get(): JsonResponse
  {
    return (new UserResource(auth()->user()))
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function update(ProfileRequest $request): JsonResponse
  {
    $user = auth()->user();
    $user->update($request->validated());

    return (new UserResource($user))
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function updatePassword(UpdatePasswordRequest $request): JsonResponse
  {
    auth()->user()->update($request->only('password'));

    return response()
      ->json(['data' => true])
      ->setStatusCode(Response::HTTP_OK);
  }
}
