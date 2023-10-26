<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
  use HasFactory, InteractsWithMedia;

  public const MEDIA_COLLECTION_NAME = 'product_images';

  protected $fillable = [
    'product_category_id',
    'product_brand_id',
    'name',
    'slug',
    'weight',
    'price',
    'stock',
    'description',
    'is_publish',
    'sex',
  ];

  protected $appends = [
    'image',
    'images',
    'is_wishlist'
  ];

  protected $casts = [
    'sex' => 'integer',
    'is_publish' => 'boolean'
  ];

  public function orderItems(): HasMany
  {
    return $this->hasMany(OrderItem::class);
  }

  public function category(): BelongsTo
  {
    return $this->belongsTo(ProductCategory::class, 'product_category_id');
  }

  public function brand(): BelongsTo
  {
    return $this->belongsTo(ProductBrand::class, 'product_brand_id');
  }

  public function registerMediaConversions(Media $media = null): void
  {
    //
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
    return  Attribute::get(fn () => $this->getFirstMediaUrl(self::MEDIA_COLLECTION_NAME) ?? null);
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
      fn ($q) => $q->withCount([
        'orderItems as sold_count' =>  fn ($q) => $q
          ->select(DB::raw('IFNULL(SUM(quantity), 0)'))
          ->whereHas('order', fn ($q) => $q->where('status', Order::STATUS_COMPLETED))
      ])
    );
  }
}
