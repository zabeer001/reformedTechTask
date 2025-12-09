<?php

namespace App\Services\Stocks;

use App\Models\Stock;
use App\Models\StockLog;
use App\Services\Images\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockAdjustmentService
{
    public function __construct(
        private readonly ImageService $imageService
    ) {
    }

    public function increaseBySku(
        string $sku,
        int $quantity,
        ?UploadedFile $image = null,
        ?int $productId = null,
        ?float $salePrice = null,
        ?float $purchasePrice = null
    ): Stock {
        return DB::transaction(function () use ($sku, $quantity, $image, $productId, $salePrice, $purchasePrice) {
            /** @var Stock|null $stock */
            $stock = Stock::where('sku', $sku)->lockForUpdate()->first();

            if (! $stock) {
                if (! $productId) {
                    throw ValidationException::withMessages([
                        'sku' => ['The specified SKU could not be found and product_id is required to create a new one.'],
                    ]);
                }

                $stock = Stock::create([
                    'product_id' => $productId,
                    'sku' => $sku,
                    'sale_price' => $salePrice ?? 0,
                    'purchase_price' => $purchasePrice ?? 0,
                    'quantity' => 0,
                    'last_update_at' => now(),
                ]);
            }

            $previous = $stock->quantity;
            if ($quantity > 0) {
                $stock->quantity += $quantity;
            }
            $stock->last_update_at = now();

            if ($image) {
                $this->imageService->delete($stock->image_path);
                $stock->image_path = $this->imageService->storeStockImage($image);
            }

            $stock->save();

            if ($quantity > 0) {
                $this->createLog($stock, 'manual-increment', $previous, $quantity);
            }

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
