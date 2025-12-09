<?php

namespace App\Services\Products;

use App\Models\Product;
use App\Services\Images\ImageService;
use Illuminate\Support\Facades\DB;

class ProductDeleteService
{
    public function __construct(
        private readonly ImageService $imageService
    ) {
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $product->stocks()->get()->each(function ($stock): void {
                $this->imageService->delete($stock->image_path);
            });

            $this->imageService->delete($product->image_path);
            $product->delete();
        });
    }
}
