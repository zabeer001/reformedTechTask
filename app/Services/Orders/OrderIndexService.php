<?php

namespace App\Services\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class OrderIndexService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function index(array $filters = []): EloquentCollection
    {
        $limit = (int) max(1, min((int) ($filters['per_page'] ?? 15), 50));
        $search = trim((string) ($filters['search'] ?? ''));
        $status = $filters['status'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        $query = Order::query()
            ->with(['products.product', 'products.stock'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('invoice_number', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($query) use ($status) {
                $normalized = strtolower($status);
                if (OrderStatus::tryFrom($normalized)) {
                    $query->where('status', $normalized);
                }
            })
            ->when($dateFrom, fn ($query) => $query->whereDate('ordered_at', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('ordered_at', '<=', $dateTo))
            ->orderByDesc('ordered_at')
            ->limit($limit);

        return $query->get();
    }
}
