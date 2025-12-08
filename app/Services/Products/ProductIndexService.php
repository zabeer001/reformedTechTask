<?php

namespace App\Services\Products;

use App\Models\Stock;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ProductIndexService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function productIndex(array $filters = []): Collection
    {
        $queryString = isset($filters['query']) ? trim((string) $filters['query']) : '';
        $limit = max(1, min((int) ($filters['limit'] ?? 10), 50));

        // Normalize filters so same meaning => same key
        $keyFilters = $filters;
        ksort($keyFilters);

        $cacheKey = 'product_index:' . md5(json_encode([
            'filters' => $keyFilters,
            'limit'   => $limit,
        ]));

        // ✅ realistic TTL for search-like listing
        $ttlSeconds = 180; // 3 minutes (tune later)

        

        return Cache::remember($cacheKey, $ttlSeconds, function () use ($filters, $queryString, $limit) {

            $query = Stock::query()
                ->with('product')
                ->where('quantity', '>', 0)
                ->when($queryString, function ($builder) use ($queryString) {
                    $builder->where(function ($query) use ($queryString) {
                        $query->whereHas('product', function ($productQuery) use ($queryString) {
                            $productQuery
                                ->where('name', 'like', "%{$queryString}%")
                                ->orWhere('barcode', 'like', "%{$queryString}%")
                                ->orWhere('slug', 'like', "%{$queryString}%");
                        })->orWhere('sku', 'like', "%{$queryString}%");
                    });
                });

            if (!empty($filters['name'])) {
                $name = trim((string) $filters['name']);
                $query->whereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$name}%"));
            }

            if (!empty($filters['barcode'])) {
                $barcode = trim((string) $filters['barcode']);
                $query->whereHas('product', fn ($pq) => $pq->where('barcode', 'like', "%{$barcode}%"));
            }

            if (!empty($filters['sku'])) {
                $sku = trim((string) $filters['sku']);
                $query->where('sku', 'like', "%{$sku}%");
            }

            if (!empty($filters['category'])) {
                $category = trim((string) $filters['category']);
                $query->whereHas('product', fn ($pq) => $pq->where('category', 'like', "%{$category}%"));
            }

            if (array_key_exists('min_sale_price', $filters) && $filters['min_sale_price'] !== null) {
                $query->where('sale_price', '>=', (float) $filters['min_sale_price']);
            }

            if (array_key_exists('max_sale_price', $filters) && $filters['max_sale_price'] !== null) {
                $query->where('sale_price', '<=', (float) $filters['max_sale_price']);
            }

            if (array_key_exists('min_purchase_price', $filters) && $filters['min_purchase_price'] !== null) {
                $query->where('purchase_price', '>=', (float) $filters['min_purchase_price']);
            }

            if (array_key_exists('max_purchase_price', $filters) && $filters['max_purchase_price'] !== null) {
                $query->where('purchase_price', '<=', (float) $filters['max_purchase_price']);
            }

            return $query
                ->orderBy('created_at')
                ->orderBy('id')
                ->limit($limit)
                ->get();
        });
    }
}
