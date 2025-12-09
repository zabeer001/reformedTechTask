<?php

namespace App\Services\Products;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\Images\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProductUpdateService
{

    use ApiResponse;

    public function __construct(
        private readonly ImageService $imageService
    ) {}

    public function productUpdate($request, $product, $imageService)
    {
        try {
            $data = $request->validated();

            // -----------------------------
            // Handle main product image (optional replace + delete old)
            // -----------------------------
            $mainImage = $request->file('image');
            if ($mainImage instanceof UploadedFile) {
                // delete old first
                $imageService->deleteProductImage($product->image_path);

                // store new
                $data['image_path'] = $imageService->storeProductImage($mainImage);
            }

            // -----------------------------
            // Update product fields
            // -----------------------------
            $product->fill($data);
            $product->save();

            // -----------------------------
            // Handle stocks
            // -----------------------------
            $stocksPayload = $data['stocks'] ?? [];
            $stockFiles    = $request->file('stocks') ?? [];

            $keepIds = [];

            if (!empty($stocksPayload) && is_array($stocksPayload)) {
                foreach ($stocksPayload as $index => $s) {

                    $stockId = $s['id'] ?? null;
                    $imagePath = null;

                    // uploaded file for this stock?
                    $file = $stockFiles[$index]['image'] ?? null;
                    if ($file instanceof UploadedFile) {
                        // if updating existing stock, delete old image
                        if ($stockId) {
                            $oldStock = $product->stocks()->where('id', $stockId)->first();
                            if ($oldStock) {
                                $imageService->deleteStockImage($oldStock->image_path);
                            }
                        }

                        $imagePath = $imageService->storeStockImage($file);
                    } else {
                        // payload-based image replacement (rare but allowed)
                        $imagePath = $s['image_path'] ?? $s['image_url'] ?? null;
                    }

                    if ($stockId) {
                        $stock = $product->stocks()->where('id', $stockId)->first();
                        if ($stock) {

                            $updateData = [
                                'sku'            => $s['sku'] ?? $stock->sku,
                                'sale_price'     => $s['sale_price'] ?? $stock->sale_price,
                                'purchase_price' => $s['purchase_price'] ?? $stock->purchase_price,
                                'quantity'       => array_key_exists('quantity', $s)
                                    ? (int) $s['quantity']
                                    : (int) $stock->quantity,
                            ];

                            // overwrite image if new one provided (file or explicit payload)
                            if ($imagePath !== null) {
                                $updateData['image_path'] = $imagePath;
                            }

                            $stock->update($updateData);
                            $keepIds[] = $stock->id;
                        }
                    } else {
                        // create new stock
                        $newStock = $product->stocks()->create([
                            'sku'            => $s['sku'] ?? null,
                            'sale_price'     => $s['sale_price'] ?? null,
                            'purchase_price' => $s['purchase_price'] ?? null,
                            'quantity'       => (int)($s['quantity'] ?? 0),
                            'image_path'     => $imagePath,
                        ]);

                        $keepIds[] = $newStock->id;
                    }
                }

                // -----------------------------
                // Delete removed stocks + their images
                // -----------------------------
                $toDelete = $product->stocks()
                    ->whereNotIn('id', $keepIds)
                    ->get();

                foreach ($toDelete as $stock) {
                    $imageService->deleteStockImage($stock->image_path);
                    $stock->delete();
                }
            }

            return $this->successResponse(
                new ProductResource($product->load('stocks')),
                'Product updated successfully.',
                200
            );
        } catch (Throwable $e) {
            report($e);
            return $this->errorResponse('Failed to update product.');
        }
    }
}
