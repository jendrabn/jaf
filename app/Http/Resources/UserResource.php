<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => (int) $this->id,
      'name' => $this->name,
      'email' => $this->email,
      'phone' => $this->phone,
      'sex' => (int) $this->sex,
      'birth_date' => $this->birth_date,
      'auth_token' => $this->whenNotNull($this->auth_token)
    ];
  }
}
