<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Middleware\CheckRole;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\TelegramBotController;
use App\Http\Controllers\Api\TelegramUserController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\UserController;

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

Route::get('/admin/products', [ProductController::class, 'adminIndex']);

//Route::middleware(['auth:sanctum', CheckRole::class.':admin'])->group(function () {

// Category routes (read-only)
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);    // List categories
    Route::get('/{id}', [CategoryController::class, 'show']); // Get single category
});

// Admin Category API routes (full CRUD)
Route::prefix('admin/categories')->middleware(['auth:sanctum', CheckRole::class.':admin'])->group(function () {
    Route::get('/', [CategoryController::class, 'index']);          // List all categories (including inactive)
    Route::post('/', [CategoryController::class, 'store']);         // Create new category
    Route::get('/{id}', [CategoryController::class, 'show']);       // Get category by ID
    Route::put('/{id}', [CategoryController::class, 'update']);     // Update category
    Route::delete('/{id}', [CategoryController::class, 'destroy']); // Delete category
});

// Tag API routes
Route::get('/tags', [TagController::class, 'index']);

// Admin routes — protected
Route::prefix('admin/tags')->middleware(['auth:sanctum', CheckRole::class.':admin'])->group(function () {
    Route::get('/', [TagController::class, 'index']);       // List all tags
    Route::post('/', [TagController::class, 'store']);      // Create new tag
    Route::get('/{id}', [TagController::class, 'show']);    // Get tag by ID
    Route::put('/{id}', [TagController::class, 'update']);  // Update tag
    Route::delete('/{id}', [TagController::class, 'destroy']); // Delete tag
});

// Public / user routes
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']); // active products only
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::get('/category/{category_id}', [ProductController::class, 'getByCategory']);
    Route::get('/tag/{tagName}', [ProductController::class, 'getByTag']);
});

// Admin routes (protected by auth & role check)
Route::prefix('admin/products')->middleware(['auth:sanctum', CheckRole::class.':admin'])->group(function () {
    Route::get('/', [ProductController::class, 'adminIndex']); // all products, no status filter
    Route::post('/', [ProductController::class, 'store']);
    Route::get('/{id}', [ProductController::class, 'show']); // optional: can reuse show
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::patch('/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);

    // Images
    Route::post('/{id}/images', [ProductController::class, 'addImage']);
    Route::delete('/{product_id}/images/{image_id}', [ProductController::class, 'deleteImage']);
});

// Favorite API routes
Route::prefix('favorites')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [FavoriteController::class, 'index']);
    Route::post('/add', [FavoriteController::class, 'add']);
    Route::delete('/remove', [FavoriteController::class, 'remove']);
});

// Cart API routes
Route::prefix('cart')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [CartController::class, 'index']);           // Get cart items
    Route::post('/add', [CartController::class, 'add']);         // Add product
    Route::post('/decrement/{id}', [CartController::class, 'decrement']);
    Route::put('/update/{id}', [CartController::class, 'update']); // Update quantity
    Route::delete('/remove/{id}', [CartController::class, 'remove']); // Remove product
});

Route::post('/telegram/webhook', [TelegramBotController::class, 'webhook']);
Route::post('/telegram-user', [TelegramUserController::class, 'storeOrLogin']);

// Public/customer route
Route::middleware(['auth:sanctum'])->post('/orders', [OrderController::class, 'store']);       // Create order (from checkout page)

// Admin routes
Route::prefix('admin/orders')->middleware(['auth:sanctum', CheckRole::class.':admin'])->group(function () {
    Route::get('/', [OrderController::class, 'index']);   // List all orders
    Route::get('/{id}', [OrderController::class, 'show']); // Get single order
    Route::delete('/{id}', [OrderController::class, 'destroy']); // Delete order
});

Route::middleware(['auth:sanctum'])->get('/user', [UserController::class, 'me']);

Route::prefix('admin')->group(function () {
    Route::get('/users/telegram/{telegramId}', [UserController::class, 'showByTelegram']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});