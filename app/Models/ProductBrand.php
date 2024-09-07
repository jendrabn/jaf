<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductBrand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('d-m-Y H:i:s');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'product_brand_id', 'id');
    }
}
