<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use DateTimeInterface;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements CanResetPassword
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';

    public const SEX_SELECT = [
        1 => 'Male',
        2 => 'Female',
    ];

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'phone',
        'sex',
        'birth_date',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'sex' => 'integer',
    ];

    protected $appends = [
        'sex_label'
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('d-m-Y H:i:s');
    }

    public function address(): HasOne
    {
        return $this->hasOne(UserAddress::class, 'user_id', 'id');
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class, 'user_id', 'id');
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class, 'user_id', 'id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }

    public function sexLabel(): Attribute
    {
        return Attribute::get(fn() => $this->attributes['sex'] ? self::SEX_SELECT[$this->attributes['sex']] : '');
    }

    public function birthDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null
        );
    }

    public function delete(): void
    {
        if ((int) $this->id === 1) {
            throw new \Exception('Cannot delete record with id = 1');
        }

        parent::delete();
    }

}
