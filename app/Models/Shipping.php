<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{
  use HasFactory;

  protected $guarded = [];

  const STATUS_PENDING = 'pending';
  const STATUS_PROCESSING = 'processing';
  const STATUS_SHIPPED = 'shipped';

  public function order()
  {
    return $this->belongsTo(Order::class);
  }
}
