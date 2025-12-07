<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'stock_id' => $this->id,
            'sku' => $this->sku,
            'quantity' => $this->quantity,
            'sale_price' => (float) $this->sale_price,
            'purchase_price' => (float) $this->purchase_price,
            'last_update_at' => optional($this->last_update_at)->toIso8601String(),
            'product' => $this->whenLoaded('product', function () {
                return [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'barcode' => $this->product->barcode,
                    'slug' => $this->product->slug,
                ];
            }),
        ];
    }
}
