<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    // GET /api/products
    public function index()
    {
        $products = Product::with('category')->get(); // eager load category
        return response()->json($products, 200);
    }

    // POST /api/products
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric',
            'category_id' => 'required|exists:categories,category_id',
            'tag_ids'     => 'array',
        ]);

        $product = Product::create($request->only('name','description','price','category_id'));

        // attach tags if provided
        if ($request->has('tag_ids')) {
            $product->tags()->attach($request->tag_ids);
        }

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product->load('tags')
        ], 201);
    }

    // GET /api/products/{id}
    public function show($id)
    {
        $product = Product::with('category')->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product, 200);
    }
    
    // GET /api/products/category/{category_id}
    public function getByCategory($category_id)
    {
        // Check if category exists
        $category = \App\Models\Category::find($category_id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        // Get all products for this category
        $products = \App\Models\Product::where('category_id', $category_id)
                                        ->with('category') // optional, eager load category
                                        ->get();

        return response()->json($products, 200);
    }

    // PUT /api/products/{id}
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'sometimes|required|numeric',
            'category_id' => 'sometimes|required|exists:categories,category_id',
            'tag_ids'     => 'array',
        ]);

        $product->update($request->only('name','description','price','category_id'));

        if ($request->has('tag_ids')) {
            $product->tags()->sync($request->tag_ids); // sync tags
        }

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product->load('tags')
        ], 200);
    }

    // DELETE /api/products/{id}
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }

}
