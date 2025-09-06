<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    // GET /api/categories
    public function index()
    {
        // Get categories with products count
        $categories = Category::withCount('products')->get();

        // Rename 'products_count' to 'productsCount' for frontend consistency
        $categories->transform(function ($category) {
            $category->productsCount = $category->products_count;
            unset($category->products_count); // optional
            return $category;
        });

        return response()->json($categories, 200);
    }

    // POST /api/categories
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = Category::create($request->all());

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category
        ], 201);
    }

    // GET /api/categories/{id}
    public function show($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json($category, 200);
    }

    // PUT /api/categories/{id}
    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
        ]);

        $category->update($request->all());

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category
        ], 200);
    }

    // DELETE /api/categories/{id}
    public function destroy($id)
    {
        $category = Category::with('products')->find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        if ($category->products->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category because it has one or more products'
            ], 400);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully'], 200);
    }


}
