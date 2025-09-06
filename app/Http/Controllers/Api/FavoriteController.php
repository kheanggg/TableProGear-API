<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Favorite;

class FavoriteController extends Controller
{
    public function index()
    {
        $userId = 1; // replace with auth()->id() later
        $favorites = Favorite::with('product.images')->where('user_id', $userId)->get();
        return response()->json($favorites);
    }

    public function add(Request $request)
    {
        $request->validate(['product_id' => 'required|integer']);
        $favorite = Favorite::firstOrCreate([
            'user_id' => 1, // replace with auth()->id()
            'product_id' => $request->product_id
        ]);

        return response()->json(['message' => 'Added to favorites', 'favorite' => $favorite]);
    }

    public function remove(Request $request)
    {
        $request->validate(['product_id' => 'required|integer']);
        $deleted = Favorite::where('user_id', 1)
            ->where('product_id', $request->product_id)
            ->delete();

        return response()->json(['message' => $deleted ? 'Removed from favorites' : 'Favorite not found']);
    }
}