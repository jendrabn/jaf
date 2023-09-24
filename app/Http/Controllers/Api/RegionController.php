<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProvinceResource;
use App\Models\Province;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RegionController extends Controller
{
  public function getAllProvinces(): JsonResponse
  {
    return ProvinceResource::collection(Province::all())
      ->response()
      ->setStatusCode(Response::HTTP_OK);
  }
}
