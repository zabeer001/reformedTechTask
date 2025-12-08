<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Requests\StockAdjustRequest;
use App\Http\Resources\ProductResource;
use App\Services\Stocks\StockAdjustmentService;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;
use Throwable;

class StockController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('role:admin');
    }

    /**
     * @OA\Post(
     *     path="/api/stocks/increase",
     *     tags={"Stocks"},
     *     summary="Increase stock quantity by SKU",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"sku","quantity"},
     *             @OA\Property(property="sku", type="string"),
     *             @OA\Property(property="quantity", type="integer", minimum=1)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Stock increased successfully.")
     * )
     */
    public function increase(StockAdjustRequest $request, StockAdjustmentService $stockAdjustmentService): JsonResponse
    {
        try {
            $stock = $stockAdjustmentService->increaseBySku(
                $request->input('sku'),
                (int) $request->input('quantity')
            );

            return $this->successResponse([
                'stock_id' => $stock->id,
                'sku' => $stock->sku,
                'quantity' => $stock->quantity,
                'product' => new ProductResource($stock->product),
            ], 'Stock increased successfully.');
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse('Failed to increase stock.');
        }
    }
}
