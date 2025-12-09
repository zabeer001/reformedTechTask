<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'product_id'     => $this->product_id,
            'sku'            => $this->sku,
            'sale_price'     => $this->sale_price,
            'purchase_price' => $this->purchase_price,
            'quantity'       => $this->quantity,
            'image_path'     => $this->image_path,
            'last_update_at' => $this->last_update_at,
        ];
    }
}
