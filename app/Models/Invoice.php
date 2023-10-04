<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
  use HasFactory;

  const STATUS_PAID = 'paid';
  const STATUS_UNPAID = 'unpaid';

  protected $guarded = [];

  public function payment(): BelongsTo
  {
    return $this->belongsTo(Payment::class);
  }

  public function order(): BelongsTo
  {
    return $this->belongsTo(Order::class);
  }
}
