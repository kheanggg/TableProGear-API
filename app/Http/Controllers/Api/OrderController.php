<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use App\Models\Cart;

class OrderController extends Controller
{
    /**
     * List all orders (optional: for admin)
     */
    public function index(Request $request)
    {
        $lastId = $request->query('last_id', 0);   // optional filter
        $limit  = $request->query('limit', 10);    // default 10 orders

        $orders = Order::with('items.product', 'customer')
            ->when($lastId > 0, function ($q) use ($lastId) {
                // since we're ordering DESC, fetch older orders
                $q->where('order_id', '<', $lastId);
            })
            ->orderBy('order_id', 'desc')
            ->take($limit)
            ->get();

        return response()->json($orders);
    }

    /**
     * Show a single order by ID
     */
    public function show($id)
    {
        $order = Order::with('items.product', 'customer')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json($order);
    }

    /**
     * Create a new order with items
     */
    public function store(Request $request)
    {
        // validate request
        $request->validate([
            'customer_id' => 'required|exists:users,id',
            'contact_name' => 'required|string',
            'phone_number' => 'required|string',
            'address' => 'required|string',
            'note' => 'nullable|string',
            'subtotal' => 'required|numeric',
            'total' => 'required|numeric',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,product_id', // or 'id' if your DB column is id
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Create the order (set order_date automatically)
            $order = Order::create([
                'customer_id' => $request->customer_id,
                'contact_name' => $request->contact_name,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'note' => $request->note,
                'order_date' => now(), // automatically set current date/time
                'subtotal' => $request->subtotal,
                'total' => $request->total,
            ]);

            // Create order items
            foreach ($request->items as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            // Clear the user's cart
            Cart::where('user_id', $request->customer_id)->delete();

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order->load('items')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $order = Order::where('order_id', $id)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }


}
