<?php

namespace App\Services\Products;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\Images\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProductStoreService
{
    use ApiResponse;

    
    public function __construct(
        private readonly ImageService $imageService
    ) {}

    /**
     * Create a product and optional stocks. The controller may pass uploaded
     * stock files in the $stockFiles array (structured like $request->file('stocks')).
     *
     * @param array $data
     * @param UploadedFile|null $image
     * @param array<int,mixed> $stockFiles
     * @return Product
     */
    public function productStore($request, $imageService)
    {
        try {
            $data = $request->validated();

            // -----------------------------
            // Handle main product image
            // -----------------------------
            // $mainImage = $request->file('image');
            // if ($mainImage instanceof UploadedFile) {
            //     $data['image_path'] = $imageService->storeProductImage($mainImage);
            // }

            // -----------------------------
            // Create product (stocks key will be ignored if not fillable)
            // -----------------------------
            $product = new Product();
            $product->fill($data);
            $product->save();

            // -----------------------------
            // Handle initial stocks (if provided)
            // -----------------------------
            $stocksPayload = $data['stocks'] ?? [];
            $stockFiles = $request->file('stocks') ?? [];

            if (!empty($stocksPayload) && is_array($stocksPayload)) {
                foreach ($stocksPayload as $index => $s) {
                    $imagePath = null;

                    // if stock image uploaded as file: stocks[index][image]
                    if (
                        isset($stockFiles[$index]['image']) &&
                        $stockFiles[$index]['image'] instanceof UploadedFile
                    ) {
                        $imagePath = $imageService->storeStockImage($stockFiles[$index]['image']);
                    } else {
                        // fallback to payload-provided path/url
                        $imagePath = $s['image_path'] ?? $s['image_url'] ?? null;
                    }

                    $product->stocks()->create([
                        'sku'            => $s['sku'] ?? null,
                        'sale_price'     => $s['sale_price'] ?? null,
                        'purchase_price' => $s['purchase_price'] ?? null,
                        'quantity'       => $s['quantity'] ?? 0,
                        'image_path'     => $imagePath,
                    ]);
                }
            }

            return $this->successResponse(
                new ProductResource($product->load('stocks')),
                'Product created successfully.',
                201
            );
        } catch (Throwable $e) {
            report($e);
            return $this->errorResponse('Failed to create product.');
        }
    }
}
