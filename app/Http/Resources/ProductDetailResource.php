<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
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
      'slug' => $this->slug,
      'images' => $this->images,
      'category' => (new ProductCategoryResource($this->category)),
      'description' => $this->description,
      'brand' => (new ProductBrandResource($this->brand)),
      'sex' => (int) $this->sex,
      'price' => $this->price,
      'stock' => $this->stock,
      'weight' => $this->weight,
      'sold_count' => $this->sold_count,
      'is_wishlist' => $this->is_wishlist,
    ];
  }
}
