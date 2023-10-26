<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentBank extends Model
{
  use HasFactory;

  protected $fillable = [
    'payment_id',
    'name',
    'account_name',
    'account_number',
  ];
}
