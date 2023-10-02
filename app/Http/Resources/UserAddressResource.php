<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAddressResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'phone' => $this->phone,
      'province' => (new ProvinceResource($this->city->province)),
      'city' => (new CityResource($this->city)),
      'district' => $this->district,
      'postal_code' => $this->postal_code,
      'address' => $this->address,
    ];
  }
}
