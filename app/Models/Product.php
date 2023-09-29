<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
  use HasFactory, InteractsWithMedia;

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
    return $this->belongsTo(ProductCategory::class, 'product_category_id');
  }

  public function brand()
  {
    return $this->belongsTo(ProductBrand::class, 'product_brand_id');
  }

  public function registerMediaConversions(Media $media = null): void
  {
    $this
      ->addMediaConversion('images')
      ->nonQueued();
  }
}
