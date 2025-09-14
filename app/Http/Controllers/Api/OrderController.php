<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * List all orders (admin only or authenticated user)
     */
    public function index(Request $request)
    {
        $user = $request->user(); // get authenticated user
        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $lastId = $request->query('last_id', 0);
        $limit  = $request->query('limit', 10);

        $orders = Order::with('items.product', 'customer')
            ->when($lastId > 0, fn($q) => $q->where('order_id', '<', $lastId))
            ->when(!$user->is_admin, fn($q) => $q->where('customer_id', $user->id))
            ->orderBy('order_id', 'desc')
            ->take($limit)
            ->get();

        return response()->json($orders, 200);
    }

    /**
     * Show a single order by ID
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $order = Order::with('items.product', 'customer')->find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Only admin or owner can view
        if (!$user->is_admin && $order->customer_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($order, 200);
    }

    /**
     * Create a new order for the authenticated user
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $request->validate([
            'contact_name' => 'required|string',
            'phone_number' => 'required|string',
            'address' => 'required|string',
            'note' => 'nullable|string',
            'subtotal' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,product_id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $order = Order::create([
                'customer_id' => $user->id,
                'contact_name' => $request->contact_name,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'note' => $request->note,
                'order_date' => now(),
                'subtotal' => $request->subtotal,
                'total' => $request->total,
            ]);

            foreach ($request->items as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            // Clear the user's cart
            Cart::where('user_id', $user->id)->delete();

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order->load('items.product')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an order
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Only admin or owner can delete
        if (!$user->is_admin && $order->customer_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $order->delete();

        return response()->json(['message' => 'Order deleted successfully'], 200);
    }
}
