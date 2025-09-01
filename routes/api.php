<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Middleware\CheckRole;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/test', function () {
    return response()->json([
        'message' => 'Successful'
    ], 200);
});

// Authentication route
Route::post('/admin/login', [AuthController::class, 'login']); // Admin Login

//Route::middleware(['auth:sanctum', CheckRole::class.':admin'])->group(function () {

// Category API routes
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);          // List all categories
    Route::post('/', [CategoryController::class, 'store']);         // Create new category
    Route::get('/{id}', [CategoryController::class, 'show']);       // Get category by ID
    Route::put('/{id}', [CategoryController::class, 'update']);     // Update category
    Route::delete('/{id}', [CategoryController::class, 'destroy']); // Delete category
});

// Tag API routes
Route::prefix('tags')->group(function () {
    Route::get('/', [TagController::class, 'index']);          // List all categories
    Route::post('/', [TagController::class, 'store']);         // Create new category
    Route::get('/{id}', [TagController::class, 'show']);       // Get category by ID
    Route::put('/{id}', [TagController::class, 'update']);     // Update category
    Route::delete('/{id}', [TagController::class, 'destroy']); // Delete category
});

// Product API routes
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::post('/', [ProductController::class, 'store']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::get('/category/{category_id}', [ProductController::class, 'getByCategory']);
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::patch('/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);

    // Images
    Route::post('/{id}/images', [ProductController::class, 'addImage']);
    Route::delete('/{product_id}/images/{image_id}', [ProductController::class, 'deleteImage']);
});
