<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Favorite;

class FavoriteController extends Controller
{
    // List favorites for authenticated user
    public function index(Request $request)
    {
        $user = $request->user(); // get user from Sanctum token
        
        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $favorites = Favorite::with('product.images')
            ->where('user_id', $user->id)
            ->get();

        return response()->json($favorites, 200);
    }

    // Add a product to favorites
    public function add(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $request->validate([
            'product_id' => 'required|integer|exists:products,product_id',
        ]);

        $favorite = Favorite::firstOrCreate([
            'user_id' => $user->id,
            'product_id' => $request->product_id,
        ]);

        return response()->json([
            'message' => 'Added to favorites',
            'favorite' => $favorite
        ], 200);
    }

    // Remove a product from favorites
    public function remove(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $request->validate([
            'product_id' => 'required|integer',
        ]);

        $deleted = Favorite::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->delete();

        return response()->json([
            'message' => $deleted ? 'Removed from favorites' : 'Favorite not found'
        ], 200);
    }
}
