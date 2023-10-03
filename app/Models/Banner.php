<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Banner extends Model implements HasMedia
{
  use HasFactory, InteractsWithMedia;

  protected $guarded = [];

  protected $appends = [
    'image',
  ];

  public function registerMediaConversions(Media $media = null): void
  {
    // No conversion
  }

  public function image(): Attribute
  {
    return Attribute::get(
      fn () => $this->getFirstMediaUrl('banner_images')
    );
  }
}
