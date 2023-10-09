<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
  use HasFactory;

  protected $guarded = [];

  const STATUS_PENDING = 'pending';
  const STATUS_CANCELLED = 'cancelled';
  const STATUS_RELEASED = 'realeased';

  public function bank()
  {
    return $this->hasOne(PaymentBank::class);
  }

  // public function ewallet()
  // {
  //   return $this->hasOne(PaymentEwallet::class);
  // }

  // public function invoice()
  // {
  //   return $this->belongsTo(Invoice::class);
  // }

  public function info(): Attribute
  {
    return Attribute::make(
      set: fn ($value) => json_encode($value),
      get: fn ($value) => json_decode($value, true)
    );
  }
}
