<?php

namespace App\Services\Products;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductDeleteService
{
    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $product->delete();
        });
    }
}
