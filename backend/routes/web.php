<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\SellerShippingController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\CustomerDashboardController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/home', [HomeController::class, 'showHome'])->name('home.show')->middleware('auth');

Route::get('/signin', [AuthController::class, 'showSignIn'])->name('signin');
Route::post('/signin', [AuthController::class, 'signIn'])->name('signin.post');
Route::get('/signup', [AuthController::class, 'showSignUp'])->name('signup');
Route::post('/signup', [AuthController::class, 'signUp'])->name('signup.post');
Route::post('/signout', [AuthController::class, 'signOut'])->name('signout');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');

Route::middleware('auth')->group(function () {
    
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('customer.dashboard');
    
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::put('/cart/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{id}', [CartController::class, 'destroy'])->name('cart.destroy');
    
    Route::get('/payment', [PaymentController::class, 'index'])->name('payment.index');
    Route::post('/payment/checkout', [PaymentController::class, 'checkout'])->name('payment.checkout');
    Route::get('/payment/{orderId}', [PaymentController::class, 'show'])->name('payment.show');
    Route::post('/payment/{orderId}/process', [PaymentController::class, 'process'])->name('payment.process');
    
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    
    Route::get('/shipping', [ShippingController::class, 'index'])->name('shipping.index');
    
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
    
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{id}', [CommentController::class, 'destroy'])->name('comments.destroy');
});

Route::middleware(['auth'])->prefix('seller')->name('seller.')->group(function () {
    Route::get('/dashboard', [SellerController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [SellerController::class, 'profile'])->name('profile');
    Route::get('/products/add', [SellerController::class, 'addProduct'])->name('add-product');
    Route::post('/products', [SellerController::class, 'storeProduct'])->name('store-product');
    Route::get('/products/{id}/edit', [SellerController::class, 'editProduct'])->name('edit-product');
    Route::put('/products/{id}', [SellerController::class, 'updateProduct'])->name('update-product');
    Route::delete('/products/{id}', [SellerController::class, 'deleteProduct'])->name('delete-product');
    Route::get('/product/{productId}/analytics', [SellerController::class, 'productAnalytics'])->name('product.analytics');
    
    // Shipping Management
    Route::get('/shipping', [SellerShippingController::class, 'index'])->name('shipping.index');
    Route::get('/shipping/{orderId}', [SellerShippingController::class, 'show'])->name('shipping.show');
    Route::put('/shipping/{orderId}/status', [SellerShippingController::class, 'updateStatus'])->name('shipping.update-status');
});

Route::get('/seller/{id}', [SellerController::class, 'show'])->name('seller.show')->where('id', '[0-9]+');

Route::get('/run-migration', function () {
    Artisan::call('migrate:fresh --seed --force');
    return 'BERHASIL! Database sudah di-reset dan di-isi data baru. Silakan cek Login sekarang.';
});