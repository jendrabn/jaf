<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
  use HasFactory;

  const STATUS_PENDING_PAYMENT = 'pending_payment';
  const STATUS_PENDING = 'pending';
  const STATUS_PROCESSING = 'processing';
  const STATUS_ON_DELIVERY = 'on_delivery';
  const STATUS_COMPLETED = 'completed';
  const STATUS_CANCELLED = 'cancelled';

  protected $guarded = [];

  protected $casts = [
    'due_date' => 'datetime'
  ];

  protected $appends = [
    'total_quantity'
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function items(): HasMany
  {
    return $this->hasMany(OrderItem::class, 'order_id');
  }

  public function invoice(): HasOne
  {
    return $this->hasOne(Invoice::class);
  }

  public function shipping(): HasOne
  {
    return $this->hasOne(Shipping::class);
  }

  public function totalQuantity(): Attribute
  {
    return Attribute::get(fn () => $this->items->reduce(fn ($carry, $item) => $carry + $item->quantity));
  }
}
