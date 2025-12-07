<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'ordered_at',
        'total_amount',
        'customer_name',
        'status',
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'status' => OrderStatus::class,
    ];

    public function products(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }
}
