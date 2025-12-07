<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductSearchRequest;
use App\Http\Resources\ProductSearchResource;
use App\Models\Stock;

class ProductSearchController extends Controller
{
    public function __invoke(ProductSearchRequest $request)
    {
        $queryString = trim((string) $request->input('query', ''));
        $limit = $request->integer('limit', 10);

        $stocks = Stock::query()
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
            })
            ->orderBy('created_at') // FIFO
            ->orderBy('id')
            ->limit($limit)
            ->get();

        return ProductSearchResource::collection($stocks);
    }
}
