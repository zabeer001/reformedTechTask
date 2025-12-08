<?php

namespace App\Services\Orders\Concerns;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Stock;
use App\Models\StockLog;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait HandlesOrderMutations
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    protected function resolveItems(array $items): Collection
    {
        $stockIds = collect($items)->pluck('stock_id');
        $stocks = Stock::with('product')
            ->whereIn('id', $stockIds)
            ->lockForUpdate()
            ->get();

        return collect($items)->map(function (array $item) use ($stocks) {
            $stock = $stocks->firstWhere('id', $item['stock_id']);

            if (! $stock) {
                throw ValidationException::withMessages([
                    'items' => ['One or more of the referenced stock items are unavailable.'],
                ]);
            }

            $quantity = (int) $item['quantity'];

            if ($quantity > $stock->quantity) {
                throw ValidationException::withMessages([
                    'items' => ["Insufficient quantity for SKU {$stock->sku}."],
                ]);
            }

            $subTotal = round((float) $stock->sale_price * $quantity, 2);
            $profitPercentage = $this->calculateProfitPercentage(
                (float) $stock->sale_price,
                (float) $stock->purchase_price
            );

            return [
                'stock' => $stock,
                'product' => $stock->product,
                'quantity' => $quantity,
                'sale_price' => $stock->sale_price,
                'sub_total' => $subTotal,
                'profit_percentage' => $profitPercentage,
            ];
        });
    }

    protected function syncOrderProducts(Order $order, Collection $items, string $logType): void
    {
        $items->each(function (array $item) use ($order, $logType) {
            /** @var Stock $stock */
            $stock = $item['stock'];
            $this->decrementStock($stock, $item['quantity'], $logType);

            $order->products()->create([
                'product_id' => $stock->product_id,
                'stock_id' => $stock->id,
                'quantity' => $item['quantity'],
                'sale_price' => $item['sale_price'],
                'sub_total' => $item['sub_total'],
                'profit_percentage' => $item['profit_percentage'],
            ]);
        });
    }

    protected function restoreOrderStock(Order $order, string $logType): void
    {
        dd($order);
        $order->loadMissing(['products.stock']);

        /** @var EloquentCollection<int, OrderProduct> $items */
        $items = $order->products;

        $items->each(function (OrderProduct $orderProduct) use ($logType) {
            if (! $orderProduct->stock) {
                return;
            }

            $this->incrementStock($orderProduct->stock, $orderProduct->quantity, $logType);
        });
    }

    protected function decrementStock(Stock $stock, int $quantity, string $logType): void
    {
        if ($stock->quantity < $quantity) {
            throw ValidationException::withMessages([
                'items' => ["Insufficient quantity for SKU {$stock->sku}."],
            ]);
        }

        $previous = $stock->quantity;
        $stock->quantity -= $quantity;
        $stock->last_update_at = now();
        $stock->save();

        $this->createLog($stock, $logType, $previous, -$quantity);
    }

    protected function incrementStock(Stock $stock, int $quantity, string $logType): void
    {
        $previous = $stock->quantity;
        $stock->quantity += $quantity;
        $stock->last_update_at = now();
        $stock->save();

        $this->createLog($stock, $logType, $previous, $quantity);
    }

    protected function createLog(Stock $stock, string $type, int $previous, int $change): void
    {
        StockLog::create([
            'type' => $type,
            'stock_id' => $stock->id,
            'product_id' => $stock->product_id,
            'previous_quantity' => $previous,
            'change_quantity' => $change,
            'current_quantity' => $stock->quantity,
        ]);
    }

    protected function calculateTotal(Collection $items): float
    {
        return round($items->sum(fn (array $item) => $item['sub_total']), 2);
    }

    protected function resolveStatus(?string $status): string
    {
        if ($status) {
            $normalized = strtolower($status);
            if (OrderStatus::tryFrom($normalized)) {
                return $normalized;
            }
        }

        return OrderStatus::Pending->value;
    }

    protected function calculateProfitPercentage(float $salePrice, float $purchasePrice): float
    {
        if ($purchasePrice <= 0.0) {
            return 0.0;
        }

        $profit = $salePrice - $purchasePrice;

        return round(($profit / $purchasePrice) * 100, 2);
    }

    protected function generateInvoiceNumber(): string
    {
        do {
            $number = 'INV-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
        } while (Order::where('invoice_number', $number)->exists());

        return $number;
    }
}
