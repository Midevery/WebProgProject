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
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArtistController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Di file ini kita definisikan semua route API yang akan dipakai React.
| Semua route ini otomatis diprefiks dengan /api oleh Laravel.
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Auth for React (session-based, gunakan middleware web untuk session)
Route::middleware('web')->group(function () {
    Route::post('/auth/signin', [AuthController::class, 'apiSignIn']);
    Route::post('/auth/signup', [AuthController::class, 'apiSignUp']);
    Route::post('/auth/signout', [AuthController::class, 'apiSignOut'])->middleware('auth');
});

// Products
Route::get('/products', [ProductController::class, 'apiIndex']);
Route::get('/products/{id}', [ProductController::class, 'apiShow']);

Route::middleware(['web', 'auth'])->group(function () {
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

    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'apiDashboard']);
        Route::get('/products', [AdminController::class, 'apiAllProducts']);
        Route::get('/products/create', [AdminController::class, 'apiAddProduct']);
        Route::post('/products', [AdminController::class, 'apiStoreProduct']);
        Route::get('/products/{id}/edit', [AdminController::class, 'apiEditProduct']);
        Route::put('/products/{id}', [AdminController::class, 'apiUpdateProduct']);
        Route::get('/orders', [AdminController::class, 'apiAllOrders']);
        Route::get('/earning', [AdminController::class, 'apiEarning']);
        Route::get('/profile', [AdminController::class, 'apiProfile']);
    });
    Route::prefix('artist')->group(function () {
        Route::get('/dashboard', [ArtistController::class, 'apiDashboard']);
        Route::get('/profile', [ArtistController::class, 'apiProfile']);
        Route::get('/product/{productId}/analytics', [ArtistController::class, 'apiProductAnalytics']);
    });
});


