<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
  use HasFactory;

  protected $guarded = [];

  public function wishlists()
  {
    return $this->hasMany(Wishlist::class);
  }

  public function carts()
  {
    return $this->hasMany(Cart::class);
  }

  public function orderItems()
  {
    return $this->hasMany(OrderItem::class);
  }

  public function category()
  {
    return $this->belongsTo(ProductCategory::class);
  }

  public function brand()
  {
    return $this->belongsTo(ProductBrand::class);
  }
}
