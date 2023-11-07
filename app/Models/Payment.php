<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
  use HasFactory;

  public const STATUS_PENDING = 'pending';
  public const STATUS_CANCELLED = 'cancelled';
  public const STATUS_RELEASED = 'realeased';

  protected $fillable = [
    'invoice_id',
    'method',
    'info',
    'amount',
    'status',
  ];

  public function bank(): HasOne
  {
    return $this->hasOne(PaymentBank::class);
  }

  public function info(): Attribute
  {
    return Attribute::make(
      set: fn ($value) => json_encode($value),
      get: fn ($value) => json_decode($value, true)
    );
  }
}
