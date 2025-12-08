<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Services\Orders\Concerns\HandlesOrderMutations;
use Illuminate\Support\Facades\DB;

class OrderStoreService
{
    use HandlesOrderMutations;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $items = $this->resolveItems($data['items']);
            $status = $this->resolveStatus($data['status'] ?? null);
            $orderedAt = $data['ordered_at'] ?? now();

            $order = Order::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'ordered_at' => $orderedAt,
                'total_amount' => $this->calculateTotal($items),
                'customer_name' => $data['customer_name'],
                'status' => $status,
            ]);

            $this->syncOrderProducts($order, $items, 'order-create');

            return $order->fresh(['products.product', 'products.stock']);
        });
    }
}
