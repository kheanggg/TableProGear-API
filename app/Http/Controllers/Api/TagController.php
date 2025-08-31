<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tag;
use App\Models\Product;

class TagController extends Controller
{
    // GET /api/tags
    public function index()
    {
        $tags = Tag::all();
        return response()->json($tags, 200);
    }

    // POST /api/tags
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        // Create the tag
        $tag = Tag::create([
            'name'        => $request->name,
            'description' => $request->description ?? null,
        ]);


        return response()->json([
            'message' => 'Tag created and attached to product successfully',
            'tag'     => $tag,
        ], 201);
    }


    // GET /api/tags/{id}
    public function show($id)
    {
        $tag = Tag::with('products')->find($id);

        if (!$tag) {
            return response()->json(['message' => 'Tag not found'], 404);
        }

        return response()->json($tag, 200);
    }

    // PUT /api/tags/{id}
    public function update(Request $request, $id)
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json(['message' => 'Tag not found'], 404);
        }

        $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string|max:255',
        ]);

        $tag->update($request->only('name', 'description'));

        return response()->json([
            'message' => 'Tag updated successfully',
            'tag'     => $tag
        ], 200);
    }

    // DELETE /api/tags/{id}
    public function destroy($id)
    {
        $tag = Tag::with('products')->find($id);

        if (!$tag) {
            return response()->json(['message' => 'Tag not found'], 404);
        }

        // Check if tag is attached to any products
        if ($tag->products->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete tag because it is attached to one or more products'
            ], 400);
        }

        $tag->delete();

        return response()->json(['message' => 'Tag deleted successfully'], 200);
    }
}
