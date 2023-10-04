<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipping extends Model
{
  use HasFactory;

  const STATUS_PENDING = 'pending';
  const STATUS_PROCESSING = 'processing';
  const STATUS_SHIPPED = 'shipped';

  const MAX_WEIGHT = 25000;
  const COURIERS = ['jne', 'tiki', 'pos'];

  protected $guarded = [];

  public function order(): BelongsTo
  {
    return $this->belongsTo(Order::class);
  }

  public function address(): Attribute
  {
    return Attribute::make(
      set: fn ($value) => json_encode($value),
      get: fn ($value) => json_decode($value, true)
    );
  }
}
