<?php

namespace App\Services\Orders;

use App\Models\Order;

class OrderShowService
{
    public function show(Order $order): Order
    {
        return $order->loadMissing(['products.product', 'products.stock']);
    }
}
