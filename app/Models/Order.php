<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    const STATUS_INITIATED = 'Iniciado';
    const STATUS_PENDING = 'Pendiente';
    const STATUS_APPROVED = 'Aprobado';
    const STATUS_IN_TRANSIT = 'En Camino';
    const STATUS_DELIVERED = 'Entregado';
    const STATUS_REJECTED = 'Rechazado';
    const STATUS_RETURN = 'Devolución';

    protected $table = 'orders';

    protected $fillable = [
        'seller_id',
        'buyer_id',
        'seller_role',
        'buyer_role',
        'status',
        'currency',
        'total',
        'coment',
        'delivery_address',
        'city',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id', 'id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id', 'id');
    }

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class, 'order_id');
    }

    public function items()
    {
        return $this->hasMany(OrderProduct::class, 'order_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_products', 'order_id', 'product_id')
            ->withPivot('quantity', 'price', 'currency')
            ->withTimestamps();
    }
}
