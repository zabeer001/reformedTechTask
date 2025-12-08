<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'barcode' => $this->barcode,
            'slug' => $this->slug,
            'category' => $this->category,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            'stocks' => $this->whenLoaded('stocks', function () {
                return $this->stocks->map(function ($stock) {
                    return [
                        'id' => $stock->id,
                        'sku' => $stock->sku,
                        'sale_price' => (float) $stock->sale_price,
                        'purchase_price' => (float) $stock->purchase_price,
                        'quantity' => $stock->quantity,
                        'last_update_at' => optional($stock->last_update_at)->toIso8601String(),
                    ];
                });
            }),
        ];
    }
}
