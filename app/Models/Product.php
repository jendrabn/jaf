<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
  use HasFactory, InteractsWithMedia;

  public const MEDIA_COLLECTION_NAME = 'product_images';

  protected $guarded = [];

  protected $appends = [
    'image',
    'images',
    'is_wishlist'
  ];

  protected $casts = [
    'sex' => 'integer',
  ];

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
    // No conversion
  }

  public function images(): Attribute
  {
    return Attribute::get(function () {
      $files = $this->getMedia(self::MEDIA_COLLECTION_NAME);

      $images = [];
      foreach ($files as $file) {
        $images[] = $file->getUrl() ?? null;
      }

      return $images;
    });
  }

  public function image(): Attribute
  {
    return  Attribute::get(
      fn () => $this->getFirstMediaUrl(self::MEDIA_COLLECTION_NAME) ?? null
    );
  }

  public function isWishlist(): Attribute
  {
    return Attribute::get(fn () => false);
  }

  public function scopePublished()
  {
    return $this->where('is_publish', true);
  }

  protected static function booted(): void
  {
    static::addGlobalScope(
      'sold_count',
      fn (Builder $builder) =>
      $builder->withCount([
        'orderItems as sold_count' =>
        fn (Builder $builder) => $builder->select(DB::raw('IFNULL(SUM(quantity), 0)'))
          ->whereHas(
            'order',
            fn (Builder $builder) => $builder->where('status', Order::STATUS_COMPLETED)
          )
      ])
    );
  }
}
