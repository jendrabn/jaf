<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';

    public const STATUSES = [
        self::STATUS_PENDING => ['label' => 'Pending'],
        self::STATUS_PROCESSING => ['label' => 'Processing'],
        self::STATUS_SHIPPED => ['label' => 'Shipped'],
    ];

    public const MAX_WEIGHT = 25000;

    public const COURIERS = ['jne', 'tiki', 'pos'];

    protected $fillable = [
        'order_id',
        'address',
        'courier',
        'courier_name',
        'service',
        'service_name',
        'etd',
        'weight',
        'tracking_number',
        'status',
    ];

    public function address(): Attribute
    {
        return Attribute::make(
            set: fn($value) => json_encode($value),
            get: fn($value) => json_decode($value, true)
        );
    }
}
