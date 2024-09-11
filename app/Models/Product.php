<?php

namespace App\Models;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public const MEDIA_COLLECTION_NAME = 'product_images';

    public const SEX_SELECT = [
        1 => 'Male',
        2 => 'Female',
        3 => 'Unisex',
    ];

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
        'is_wishlist',
        'sex_label',
    ];

    protected $casts = [
        'sex' => 'integer',
        'is_publish' => 'boolean',
        'sold_count' => 'integer',
    ];

    public function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d-m-Y H:i:s');
    }

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

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('preview')->fit(Fit::Crop, 120, 120)->nonQueued();
    }

    public function images(): Attribute
    {
        return Attribute::get(function () {
            $files = $this->getMedia(self::MEDIA_COLLECTION_NAME);

            $files->each(function ($item) {
                $item->url = $item->getUrl();
                $item->preview = $item->getUrl('preview');
            });

            return $files;
        });
    }

    public function image(): Attribute
    {
        return Attribute::get(function () {
            $file = $this->getFirstMedia(self::MEDIA_COLLECTION_NAME);

            if ($file) {
                $file->url = $file->getUrl();
                $file->preview = $file->getUrl('preview');
            }

            return $file;
        });
    }

    public function isWishlist(): Attribute
    {
        return Attribute::get(fn() => false);
    }

    public function scopePublished()
    {
        return $this->where('is_publish', true);
    }

    protected static function booted(): void
    {
        static::addGlobalScope(
            'sold_count',
            fn($q) => $q->withCount([
                'orderItems as sold_count' => fn($q) => $q
                    ->select(DB::raw('IFNULL(SUM(quantity), 0)'))
                    ->whereHas('order', fn($q) => $q->where('status', Order::STATUS_COMPLETED))
            ])
        );
    }

    public function sexLabel(): Attribute
    {
        return Attribute::get(fn() => $this->attributes['sex'] ? self::SEX_SELECT[$this->attributes['sex']] : '');
    }
}
