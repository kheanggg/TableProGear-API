<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;

class CartController extends Controller
{
    // List all cart items for a given user
    public function index(Request $request)
    {
        $userId = $request->query('user_id'); // get user_id from query
        if (!$userId) {
            return response()->json(['message' => 'User ID is required'], 400);
        }

        $cartItems = Cart::with(['product', 'product.images' => function($q) {
                $q->orderBy('sort_order', 'asc');
            }])
            ->where('user_id', $userId)
            ->get();

        return response()->json($cartItems, 200);
    }

    // Add a product to the cart
    public function add(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id', // <-- added
            'product_id' => 'required|integer|exists:products,product_id',
            'quantity' => 'sometimes|integer|min:1',
        ]);

        $userId = $request->user_id;

        $cartItem = Cart::firstOrCreate(
            ['user_id' => $userId, 'product_id' => $request->product_id],
            ['quantity' => $request->quantity ?? 1]
        );

        // If it already exists, increment quantity
        if (!$cartItem->wasRecentlyCreated) {
            $cartItem->increment('quantity', $request->quantity ?? 1);
        }

        return response()->json(['message' => 'Product added to cart', 'cart' => $cartItem], 200);
    }

    public function decrement(Request $request, $id)
    {
        $cartItem = Cart::findOrFail($id);

        if ($cartItem->quantity > 1) {
            $cartItem->decrement('quantity', 1);
        } else {
            $cartItem->delete();
        }

        return response()->json(['message' => 'Cart updated', 'cart' => $cartItem], 200);
    }

    // Update quantity
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = Cart::findOrFail($id);
        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json(['message' => 'Cart updated', 'cart' => $cartItem], 200);
    }

    // Remove item from cart
    public function remove($id)
    {
        $cartItem = Cart::findOrFail($id);
        $cartItem->delete();

        return response()->json(['message' => 'Item removed from cart'], 200);
    }
}
