<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements CanResetPassword
{
  use HasApiTokens, HasFactory, Notifiable, HasRoles;

  public const ROLE_ADMIN = 'admin';
  public const ROLE_USER = 'user';

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
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
    'sex' => 'integer'
  ];

  public function address(): HasOne
  {
    return $this->hasOne(UserAddress::class);
  }

  // public function wishlists(): HasMany
  // {
  //   return $this->hasMany(Wishlist::class);
  // }

  // public function carts(): HasMany
  // {
  //   return $this->hasMany(Cart::class);
  // }

  // public function orders(): HasMany
  // {
  //   return $this->hasMany(Order::class);
  // }
}
