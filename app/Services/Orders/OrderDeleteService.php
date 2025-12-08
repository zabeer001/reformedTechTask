<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Services\Orders\Concerns\HandlesOrderMutations;
use Illuminate\Support\Facades\DB;

class OrderDeleteService
{
    use HandlesOrderMutations;

    public function delete(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $this->restoreOrderStock($order, 'order-delete');
            $order->products()->delete();
            $order->delete();
        });
    }
}
