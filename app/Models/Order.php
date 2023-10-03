<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
  use HasFactory;

  protected $guarded = [];

  const STATUS_PENDING_PAYMENT = 'pending_payment';
  const STATUS_PENDING = 'pending';
  const STATUS_PROCESSING = 'processing';
  const STATUS_ON_DELIVERY = 'on_delivery';
  const STATUS_COMPLETED = 'completed';
  const STATUS_CANCELLED = 'cancelled';

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function items()
  {
    return $this->hasMany(OrderItem::class, 'order_id');
  }

  public function invoice()
  {
    return $this->hasOne(Invoice::class);
  }

  public function shipping()
  {
    return $this->hasOne(Shipping::class);
  }
}
