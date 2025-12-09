<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;
use Throwable;

class AdminProductController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *     path="/api/admin/products",
     *     tags={"Products"},
     *     summary="List products for admin",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Products retrieved successfully.")
     * )
     */
    public function __invoke(): JsonResponse
    {
        try {
            $products = Product::query()
                ->with(['stocks' => function ($query): void {
                    $query->orderBy('created_at');
                }])
                ->orderByDesc('created_at')
                ->get();

            return $this->successResponse(
                ProductResource::collection($products),
                'Products retrieved successfully.'
            );
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse('Failed to retrieve admin products.');
        }
    }
}
