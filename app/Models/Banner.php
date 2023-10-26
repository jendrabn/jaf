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

  public const MEDIA_COLLECTION_NAME = 'banner_images';

  protected $fillable = [
    'image_alt',
    'url',
  ];

  protected $appends = [
    'image',
  ];

  public function registerMediaConversions(Media $media = null): void
  {
    $this->addMediaConversion('thumb')->fit('crop', 50, 50);
    $this->addMediaConversion('preview')->fit('crop', 120, 120);
  }

  public function image(): Attribute
  {
    return Attribute::get(function () {
      $file = $this->getFirstMedia(self::MEDIA_COLLECTION_NAME);

      if ($file) {
        $file->url = $file->getUrl();
        $file->thumbnail = $file->getUrl('thumb');
        $file->preview = $file->getUrl('preview');
      }

      return $file;
    });
  }
}
