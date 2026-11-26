<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    const ROLE_ADMIN = 'Administrador';

    const ROLE_DISTRIBUTOR = 'Distribuidor';

    const ROLE_IRONMONGER = 'Ferretero';

    protected $table = 'users';

    protected $fillable = [
        'name',
        'document_type',
        'document_number',
        'phone',
        'email',
        'country',
        'city',
        'role',
        'image',
        'establishment_name',
        'password',
        'price_list_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function priceList()
    {
        return $this->belongsTo(PriceList::class, 'price_list_id');
    }

    public function salesOrders()
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isDistributor(): bool
    {
        return $this->role === self::ROLE_DISTRIBUTOR;
    }

    public function isIronmonger(): bool
    {
        return $this->role === self::ROLE_IRONMONGER;
    }
}
