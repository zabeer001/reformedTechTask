<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Services\Orders\Concerns\HandlesOrderMutations;

class OrderPlaceService
{
    use HandlesOrderMutations;

    public function place(Order $order, ?string $status = null): Order
    {
        $resolvedStatus = $this->resolveStatus($status ?? $order->status?->value);

        $order->update([
            'status' => $resolvedStatus,
        ]);

        return $order->fresh(['products.product', 'products.stock']);
    }
}
