<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Services\Orders\Concerns\HandlesOrderMutations;
use Illuminate\Support\Facades\DB;

class OrderUpdateService
{
    use HandlesOrderMutations;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data) {
            $items = $this->resolveItems($data['items']);
            $status = $this->resolveStatus($data['status'] ?? $order->status?->value);
            $orderedAt = $data['ordered_at'] ?? $order->ordered_at;

            $this->restoreOrderStock($order, 'order-update');

            $order->update([
                'ordered_at' => $orderedAt,
                'total_amount' => $this->calculateTotal($items),
                'customer_name' => $data['customer_name'],
                'status' => $status,
            ]);

            $order->products()->delete();

            $this->syncOrderProducts($order, $items, 'order-update');

            return $order->fresh(['products.product', 'products.stock']);
        });
    }
}
