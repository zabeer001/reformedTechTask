<?php

namespace App\Services\Products;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductStoreService
{
    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create($data);

            return $product->fresh(['stocks']);
        });
    }
}
