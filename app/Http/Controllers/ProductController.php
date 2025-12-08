<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Requests\ProductIndexRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\Products\ProductIndexService;
use App\Services\Products\ProductStoreService;
use App\Services\Products\ProductDeleteService;
use App\Services\Products\ProductShowService;
use App\Services\Products\ProductUpdateService;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;
use Throwable;

class ProductController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth:api')->except(['index', 'show']);
        $this->middleware('role:admin')->only(['store', 'update', 'destroy']);
    }

    /**
     * @OA\Get(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="Search or list products",
     *     @OA\Parameter(name="query", in="query", description="Fuzzy search term", @OA\Schema(type="string")),
     *     @OA\Parameter(name="limit", in="query", description="Maximum number of results", @OA\Schema(type="integer", minimum=1, maximum=50)),
     *     @OA\Parameter(name="name", in="query", description="Filter by product name", @OA\Schema(type="string")),
     *     @OA\Parameter(name="barcode", in="query", description="Filter by barcode", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sku", in="query", description="Filter by stock SKU", @OA\Schema(type="string")),
     *     @OA\Parameter(name="category", in="query", description="Filter by category", @OA\Schema(type="string")),
     *     @OA\Parameter(name="min_sale_price", in="query", description="Minimum sale price", @OA\Schema(type="number", format="float")),
     *     @OA\Parameter(name="max_sale_price", in="query", description="Maximum sale price", @OA\Schema(type="number", format="float")),
     *     @OA\Response(response=200, description="Products retrieved successfully.")
     * )
     */
    public function index(ProductIndexRequest $request, ProductIndexService $productIndexService): JsonResponse
    {
        try {
            $stocks = $productIndexService->productIndex($request->validated());

            $products = $stocks->pluck('product')->unique('id')->values();

            return $this->successResponse(
                ProductResource::collection($products),
                'Products retrieved successfully.'
            );
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse('Failed to retrieve products.');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="Create a product",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","barcode"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="barcode", type="string", maxLength=255),
     *             @OA\Property(property="slug", type="string", maxLength=255),
     *             @OA\Property(property="category", type="string", maxLength=255)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Product created successfully.")
     * )
     */
    public function store(StoreProductRequest $request, ProductStoreService $productStoreService): JsonResponse
    {
        try {
            $product = $productStoreService->create($request->validated());

            return $this->successResponse(
                new ProductResource($product),
                'Product created successfully.',
                201
            );
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse('Failed to create product.');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/products/{product}",
     *     tags={"Products"},
     *     summary="Show product details",
     *     @OA\Parameter(name="product", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Product details retrieved successfully.")
     * )
     */
    public function show(Product $product, ProductShowService $productShowService): JsonResponse
    {
        try {
            $product = $productShowService->show($product);

            return $this->successResponse(
                new ProductResource($product),
                'Product details retrieved successfully.'
            );
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse('Failed to retrieve product details.');
        }
    }

    /**
     * @OA\Put(
     *     path="/api/products/{product}",
     *     tags={"Products"},
     *     summary="Update a product",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="product", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","barcode"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="barcode", type="string", maxLength=255),
     *             @OA\Property(property="slug", type="string", maxLength=255),
     *             @OA\Property(property="category", type="string", maxLength=255)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Product updated successfully.")
     * )
     */
    public function update(
        UpdateProductRequest $request,
        Product $product,
        ProductUpdateService $productUpdateService
    ): JsonResponse {
        try {
            $product = $productUpdateService->update($product, $request->validated());

            return $this->successResponse(
                new ProductResource($product),
                'Product updated successfully.'
            );
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse('Failed to update product.');
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{product}",
     *     tags={"Products"},
     *     summary="Delete a product",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="product", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Product deleted successfully.")
     * )
     */
    public function destroy(Product $product, ProductDeleteService $productDeleteService): JsonResponse
    {
        try {
            $productDeleteService->delete($product);

            return $this->successResponse(null, 'Product deleted successfully.');
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse('Failed to delete product.');
        }
    }
}
