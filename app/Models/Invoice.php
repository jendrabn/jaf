<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
  use HasFactory;

  protected $guarded = [];

  const STATUS_PAID = 'paid';
  const STATUS_UNPAID = 'unpaid';

  public function payment()
  {
    return $this->belongsTo(Payment::class);
  }

  public function order()
  {
    return $this->belongsTo(Order::class);
  }
}
