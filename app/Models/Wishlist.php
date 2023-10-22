<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
  use HasFactory;

  protected $guarded = [];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function product(): BelongsTo
  {
    return $this->belongsTo(Product::class);
  }

  protected static function booted(): void
  {
    static::addGlobalScope(
      fn ($q) => $q->whereHas('product', fn ($q) => $q->where('is_publish', true))
    );
  }
}
