<?php

namespace App\Services\Stocks;

use App\Models\Stock;
use App\Models\StockLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockAdjustmentService
{
    public function increaseBySku(string $sku, int $quantity): Stock
    {
        return DB::transaction(function () use ($sku, $quantity) {
            /** @var Stock|null $stock */
            $stock = Stock::where('sku', $sku)->lockForUpdate()->first();

            if (! $stock) {
                throw ValidationException::withMessages([
                    'sku' => ['The specified SKU could not be found.'],
                ]);
            }

            $previous = $stock->quantity;
            $stock->quantity += $quantity;
            $stock->last_update_at = now();
            $stock->save();

            $this->createLog($stock, 'manual-increment', $previous, $quantity);

            return $stock->fresh('product');
        });
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
}
