<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
  use HasFactory, InteractsWithMedia;

  protected $guarded = [];

  protected $appends = ['image', 'images', 'is_wishlist'];

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

  public function images(): Attribute
  {
    $files = $this->getMedia('images');

    $images = [];
    foreach ($files as $file) {
      $images[] = $file->getUrl('images');
    }

    return Attribute::get(fn () => $images);
  }

  public function image(): Attribute
  {
    $file = $this->getFirstMedia('images');

    return  Attribute::get(fn () => $file ? $file->getUrl('images') : null);
  }

  public function isWishlist(): Attribute
  {
    return Attribute::get(fn () => false);
  }
}
