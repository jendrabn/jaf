<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentBank extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'name',
        'account_name',
        'account_number',
    ];

    // public function payment(): BelongsTo
    // {
    //   return $this->belongsTo(Payment::class);
    // }
}
