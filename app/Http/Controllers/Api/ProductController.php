<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;

class ProductController extends Controller
{
    // GET /api/products
    public function index()
    {
        $products = Product::with(['category', 'tags', 'images'])->get(); // eager load everything
        return response()->json($products, 200);
    }

    // GET /api/products/{id}
    public function show($id)
    {
        $product = Product::with(['category', 'tags', 'images'])->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product, 200);
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
            'tag_ids.*'   => 'exists:tags,tag_id',
            'images'      => 'array|max:5',
            'images.*.image_url'   => 'required|string|max:255',
            'images.*.is_featured' => 'boolean',
            'images.*.sort_order'  => 'integer',
        ]);

        // Check featured images in request
        if ($request->has('images')) {
            $featuredCount = collect($request->images)
                ->filter(fn($img) => isset($img['is_featured']) && $img['is_featured'])
                ->count();
            if ($featuredCount > 1) {
                return response()->json(['message' => 'Only one featured image is allowed per product'], 422);
            }
        }

        // Create product
        $product = Product::create($request->only('name','description','price','category_id'));

        // Attach tags if provided
        if ($request->has('tag_ids')) {
            $product->tags()->attach($request->tag_ids);
        }

        // Attach images
        if ($request->has('images')) {
            foreach ($request->images as $index => $img) {
                $isFeatured = $img['is_featured'] ?? false;

                // Reset existing featured images (safe for new product, just precaution)
                if ($isFeatured) {
                    $product->images()->where('is_featured', true)->update(['is_featured' => false]);
                }

                $product->images()->create([
                    'image_url'   => $img['image_url'],
                    'is_featured' => $isFeatured,
                    'sort_order'  => $img['sort_order'] ?? $index,
                ]);
            }
        }

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product->load(['tags', 'images' => fn($q) => $q->orderBy('sort_order')])
        ], 201);
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
            'tag_ids.*'   => 'exists:tags,tag_id',
            'images'      => 'array|max:5',
            'images.*.id'          => 'sometimes|exists:product_images,id',
            'images.*.image_url'   => 'required|string|max:255',
            'images.*.is_featured' => 'boolean',
            'images.*.sort_order'  => 'integer',
        ]);

        // Check featured images count in request
        if ($request->has('images')) {
            $featuredCount = collect($request->images)
                ->filter(fn($img) => isset($img['is_featured']) && $img['is_featured'])
                ->count();
            if ($featuredCount > 1) {
                return response()->json(['message' => 'Only one featured image is allowed per product'], 422);
            }
        }

        // Update product info
        $product->update($request->only('name','description','price','category_id'));

        // Sync tags
        if ($request->has('tag_ids')) {
            $product->tags()->sync($request->tag_ids);
        }

        // Update or create images
        if ($request->has('images')) {
            foreach ($request->images as $index => $img) {
                $isFeatured = $img['is_featured'] ?? false;

                // Reset existing featured images except current
                if ($isFeatured) {
                    $product->images()
                        ->where('is_featured', true)
                        ->when(isset($img['id']), fn($q) => $q->where('id', '!=', $img['id']))
                        ->update(['is_featured' => false]);
                }

                $data = [
                    'image_url'   => $img['image_url'],
                    'is_featured' => $isFeatured,
                    'sort_order'  => $img['sort_order'] ?? $index,
                ];

                if (isset($img['id'])) {
                    // Update existing image
                    $image = $product->images()->find($img['id']);
                    if ($image) {
                        $image->update($data);
                    }
                } else {
                    // Create new image
                    $product->images()->create($data);
                }
            }
        }

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product->load(['tags', 'images' => fn($q) => $q->orderBy('sort_order')])
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

    // POST /api/products/{id}/images
    public function addImage(Request $request, $product_id)
    {
        $request->validate([
            'image_url'   => 'required|string|max:255',
            'is_featured' => 'sometimes|boolean',
        ]);

        $product = Product::find($product_id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Optional: limit total images to 5
        if ($product->images()->count() >= 5) {
            return response()->json(['message' => 'Maximum 5 images allowed per product'], 400);
        }

        // Reset previous featured image if new one is featured
        if ($request->is_featured) {
            $product->images()->where('is_featured', true)->update(['is_featured' => false]);
        }

        $image = $product->images()->create([
            'image_url'   => $request->image_url,
            'is_featured' => $request->is_featured ?? false,
        ]);

        return response()->json(['message' => 'Image added successfully', 'image' => $image], 201);
    }

    // DELETE /api/products/{product_id}/images/{image_id}
    public function deleteImage($product_id, $image_id)
    {
        $product = Product::find($product_id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $image = $product->images()->find($image_id);
        if (!$image) {
            return response()->json(['message' => 'Image not found'], 404);
        }

        $image->delete();

        return response()->json(['message' => 'Image deleted successfully'], 200);
    }
}