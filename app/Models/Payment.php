<?php

namespace App\Models;

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
    return $this->belongsTo(PaymentBank::class);
  }

  public function ewallet()
  {
    return $this->belongsTo(PaymentEwallet::class);
  }

  public function invoice()
  {
    return $this->belongsTo(Invoice::class);
  }
}
