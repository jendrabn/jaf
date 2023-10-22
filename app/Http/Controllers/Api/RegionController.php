<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\{CityResource, ProvinceResource};
use App\Models\Province;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RegionController extends Controller
{
  public function provinces(): JsonResponse
  {
    return ProvinceResource::collection(Province::all())
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }

  public function cities(Province $province): JsonResponse
  {
    return CityResource::collection($province->cities)
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }
}
