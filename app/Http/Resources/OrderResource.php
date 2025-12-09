<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'ordered_at' => optional($this->ordered_at)->toIso8601String(),
            'total_amount' => (float) $this->total_amount,
            'customer_name' => $this->customer_name,
            'status' => $this->status instanceof \UnitEnum ? $this->status->value : $this->status,
            'items' => $this->whenLoaded('products', function () {
                return $this->products->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'stock_id' => $item->stock_id,
                        'quantity' => $item->quantity,
                        'sale_price' => (float) $item->sale_price,
                        'sub_total' => (float) $item->sub_total,
                        'profit_percentage' => (float) $item->profit_percentage,
                        'product' => $item->relationLoaded('product') ? [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'barcode' => $item->product->barcode,
                            'slug' => $item->product->slug,
                            'image_url' => $item->product->image_path ?: null,
                        ] : null,
                        'stock' => $item->relationLoaded('stock') ? [
                            'id' => $item->stock->id,
                            'sku' => $item->stock->sku,
                            'quantity' => $item->stock->quantity,
                            'image_url' => $item->stock->image_path ?: null,
                        ] : null,
                    ];
                });
            }),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
