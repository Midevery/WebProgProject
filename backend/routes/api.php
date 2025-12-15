<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\SellerShippingController;

Route::post('/auth/signin', [AuthController::class, 'apiSignIn']);
Route::post('/auth/signup', [AuthController::class, 'apiSignUp']);

Route::get('/products', [ProductController::class, 'apiIndex']);
Route::get('/products/{id}', [ProductController::class, 'apiShow']);

Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/auth/signout', [AuthController::class, 'apiSignOut']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::get('/me', [ProfileController::class, 'apiMe']);
    Route::put('/me', [ProfileController::class, 'apiUpdate']);
    Route::put('/me/password', [ProfileController::class, 'apiUpdatePassword']);

    Route::get('/dashboard', [CustomerDashboardController::class, 'apiIndex']);

    Route::get('/cart', [CartController::class, 'apiIndex']);
    Route::post('/cart', [CartController::class, 'apiStore']);
    Route::put('/cart/{id}', [CartController::class, 'apiUpdate']);
    Route::delete('/cart/{id}', [CartController::class, 'apiDestroy']);

    Route::get('/wishlist', [WishlistController::class, 'apiIndex']);
    Route::post('/wishlist', [WishlistController::class, 'apiStore']);
    Route::delete('/wishlist/{id}', [WishlistController::class, 'apiDestroyApi']);
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle']);

    Route::get('/shipping', [ShippingController::class, 'apiIndex']);
    Route::get('/shipping/{orderId}', [ShippingController::class, 'apiShow']);

    Route::get('/payment/preview', [PaymentController::class, 'apiPreview']);
    Route::post('/payment/checkout', [PaymentController::class, 'apiCheckout']);
    Route::get('/payment/{orderId}', [PaymentController::class, 'apiShow']);
    Route::post('/payment/{orderId}/process', [PaymentController::class, 'apiProcess']);

    Route::post('/comments', [CommentController::class, 'apiStore']);
    Route::delete('/comments/{id}', [CommentController::class, 'apiDestroy']);
    
    Route::prefix('seller')->group(function () {
        Route::get('/dashboard', [SellerController::class, 'apiDashboard']);
        Route::get('/profile', [SellerController::class, 'apiProfile']);
        Route::get('/products/add', [SellerController::class, 'addProduct']);
        Route::post('/products', [SellerController::class, 'storeProduct']);
        Route::get('/products/{id}/edit', [SellerController::class, 'editProduct']);
        Route::put('/products/{id}', [SellerController::class, 'updateProduct']);
        Route::delete('/products/{id}', [SellerController::class, 'deleteProduct']);
        Route::get('/product/{productId}/analytics', [SellerController::class, 'apiProductAnalytics']);
        Route::get('/shipping', [SellerShippingController::class, 'index']);
        Route::get('/shipping/{orderId}', [SellerShippingController::class, 'show']);
        Route::put('/shipping/{orderId}/status', [SellerShippingController::class, 'updateStatus']);
    });
});