<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;

class CartController extends Controller
{
    // List all cart items for authenticated user
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $cartItems = Cart::with(['product', 'product.images' => function ($q) {
            $q->orderBy('sort_order', 'asc');
        }])->where('user_id', $user->id)->get();

        return response()->json($cartItems, 200);
    }

    // Add a product to the cart
    public function add(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $request->validate([
            'product_id' => 'required|integer|exists:products,product_id',
            'quantity' => 'sometimes|integer|min:1',
        ]);

        $cartItem = Cart::firstOrCreate(
            ['user_id' => $user->id, 'product_id' => $request->product_id],
            ['quantity' => $request->quantity ?? 1]
        );

        // If it already exists, increment quantity
        if (!$cartItem->wasRecentlyCreated) {
            $cartItem->increment('quantity', $request->quantity ?? 1);
        }

        return response()->json([
            'message' => 'Product added to cart',
            'cart' => $cartItem
        ], 200);
    }

    // Decrement product quantity or remove if 1
    public function decrement(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $cartItem = Cart::where('id', $id)->where('user_id', $user->id)->firstOrFail();

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
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = Cart::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json(['message' => 'Cart updated', 'cart' => $cartItem], 200);
    }

    // Remove item from cart
    public function remove(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $cartItem = Cart::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        $cartItem->delete();

        return response()->json(['message' => 'Item removed from cart'], 200);
    }
}
