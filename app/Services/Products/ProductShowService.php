<?php

namespace App\Services\Products;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductShowService
{
    public function show(Product $product): Product
    {
        $cacheKey = "product_show:{$product->id}";
        $ttlSeconds = 180; // 3 minutes (tune as needed)

        return Cache::remember($cacheKey, $ttlSeconds, function () use ($product) {
            // re-fetch fresh from DB and eager load stocks
            return Product::query()
                ->with('stocks')
                ->findOrFail($product->id);
        });
    }
}
