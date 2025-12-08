<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Requests\OrderIndexRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\Orders\OrderDeleteService;
use App\Services\Orders\OrderIndexService;
use App\Services\Orders\OrderPlaceService;
use App\Services\Orders\OrderShowService;
use App\Services\Orders\OrderStoreService;
use App\Services\Orders\OrderUpdateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;
use Throwable;

class OrderController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth:api')->except(['store']);
        $this->middleware('role:admin')->only([
            'update',
            'destroy',
            'place',
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     tags={"Orders"},
     *     summary="List orders",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", minimum=1, maximum=50)),
     *     @OA\Parameter(name="search", in="query", description="Search by customer or invoice", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", description="Filter by status", @OA\Schema(type="string")),
     *     @OA\Parameter(name="date_from", in="query", description="Filter start date", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", description="Filter end date", @OA\Schema(type="string", format="date")),
     *     @OA\Response(response=200, description="Orders retrieved successfully.")
     * )
     */
    public function index(OrderIndexRequest $request, OrderIndexService $orderIndexService): JsonResponse
    {
        try {
            $orders = $orderIndexService->index($request->validated());

            return $this->successResponse(
                OrderResource::collection($orders),
                'Orders retrieved successfully.'
            );
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse('Failed to retrieve orders.');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     tags={"Orders"},
     *     summary="Create an order",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"customer_name","items"},
     *             @OA\Property(property="customer_name", type="string", maxLength=255),
     *             @OA\Property(property="ordered_at", type="string", format="date-time"),
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     required={"stock_id","quantity"},
     *                     @OA\Property(property="stock_id", type="integer"),
     *                     @OA\Property(property="quantity", type="integer", minimum=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Order created successfully.")
     * )
     */
    public function store(StoreOrderRequest $request, OrderStoreService $orderStoreService): JsonResponse
    {
        try {
            $order = $orderStoreService->create($request->validated());

            return $this->successResponse(
                new OrderResource($order),
                'Order created successfully.',
                201
            );
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse('Failed to create order.');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{order}",
     *     tags={"Orders"},
     *     summary="Show order details",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Order details retrieved successfully.")
     * )
     */
    public function show(Order $order, OrderShowService $orderShowService): JsonResponse
    {
        try {
            $order = $orderShowService->show($order);

            return $this->successResponse(
                new OrderResource($order),
                'Order details retrieved successfully.'
            );
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse('Failed to retrieve order details.');
        }
    }

    /**
     * @OA\Put(
     *     path="/api/orders/{order}",
     *     tags={"Orders"},
     *     summary="Update an order",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"customer_name","items"},
     *             @OA\Property(property="customer_name", type="string", maxLength=255),
     *             @OA\Property(property="ordered_at", type="string", format="date-time"),
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     required={"stock_id","quantity"},
     *                     @OA\Property(property="stock_id", type="integer"),
     *                     @OA\Property(property="quantity", type="integer", minimum=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Order updated successfully.")
     * )
     */
    public function update(
        UpdateOrderRequest $request,
        Order $order,
        OrderUpdateService $orderUpdateService
    ): JsonResponse {
        try {
            $order = $orderUpdateService->update($order, $request->validated());

            return $this->successResponse(
                new OrderResource($order),
                'Order updated successfully.'
            );
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse('Failed to update order.');
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/orders/{order}",
     *     tags={"Orders"},
     *     summary="Delete an order",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Order deleted successfully.")
     * )
     */
    public function destroy(Order $order, OrderDeleteService $orderDeleteService): JsonResponse
    {
        try {
            $orderDeleteService->delete($order);

            return $this->successResponse(null, 'Order deleted successfully.');
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse('Failed to delete order.');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/orders/{order}/place",
     *     tags={"Orders"},
     *     summary="Update order status",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Order status updated successfully.")
     * )
     */
    public function place(Request $request, Order $order, OrderPlaceService $orderPlaceService): JsonResponse
    {
        try {
            $payload = $request->validate([
                'status' => ['nullable', Rule::in(OrderStatus::values())],
            ]);

            $order = $orderPlaceService->place($order, $payload['status'] ?? null);

            return $this->successResponse(
                new OrderResource($order),
                'Order status updated successfully.'
            );
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse('Failed to update order status.');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/orders/{order}/fake-payment",
     *     tags={"Orders"},
     *     summary="Simulate order payment",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Fake payment processed successfully.")
     * )
     */
    public function fakePayment(Order $order): JsonResponse
    {
        try {
            return $this->successResponse([
                'invoice_number' => $order->invoice_number,
                'status' => 'paid',
            ], 'Fake payment processed successfully.');
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse('Failed to process fake payment.');
        }
    }
}
