<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\Orders\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) max(1, min($request->integer('per_page', 15), 50));
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $orders = Order::query()
            ->with(['products.product', 'products.stock'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('invoice_number', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($query) use ($status) {
                $normalized = strtolower($status);
                if (OrderStatus::tryFrom($normalized)) {
                    $query->where('status', $normalized);
                }
            })
            ->when($dateFrom, fn ($query) => $query->whereDate('ordered_at', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('ordered_at', '<=', $dateTo))
            ->orderByDesc('ordered_at')
            ->paginate($perPage)
            ->withQueryString();

        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request, OrderService $orderService): OrderResource
    {
        $order = $orderService->create($request->validated());

        return new OrderResource($order);
    }

    public function show(Order $order): OrderResource
    {
        return new OrderResource($order->loadMissing(['products.product', 'products.stock']));
    }

    public function update(UpdateOrderRequest $request, Order $order, OrderService $orderService): OrderResource
    {
        $order = $orderService->update($order, $request->validated());

        return new OrderResource($order);
    }

    public function destroy(Order $order, OrderService $orderService): JsonResponse
    {
        $orderService->delete($order);

        return response()->json(['message' => 'Order deleted successfully.']);
    }

    public function place(Request $request, Order $order, OrderService $orderService): OrderResource
    {
        $payload = $request->validate([
            'status' => ['nullable', Rule::in(OrderStatus::values())],
        ]);

        $order = $orderService->place($order, $payload['status'] ?? null);

        return new OrderResource($order);
    }

    public function fakePayment(Order $order): JsonResponse
    {
        return response()->json([
            'invoice_number' => $order->invoice_number,
            'status' => 'paid',
            'message' => 'Fake payment processed successfully.',
        ]);
    }
}
