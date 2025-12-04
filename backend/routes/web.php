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
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\AdminController;

Route::get('/', [HomeController::class, 'index'])->name('home');

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
    Route::get('/shipping/track/{orderId}', [ShippingController::class, 'tracking'])->name('shipping.tracking');
    
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
    
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{id}', [CommentController::class, 'destroy'])->name('comments.destroy');
});

Route::middleware(['auth'])->prefix('artist')->name('artist.')->group(function () {
    Route::get('/dashboard', [ArtistController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [ArtistController::class, 'profile'])->name('profile');
    Route::get('/product/{productId}/analytics', [ArtistController::class, 'productAnalytics'])->name('product.analytics');
});

Route::get('/artist/{id}', [ArtistController::class, 'show'])->name('artist.show')->where('id', '[0-9]+');

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
    Route::get('/products', [AdminController::class, 'allProducts'])->name('all-products');
    Route::get('/products/add', [AdminController::class, 'addProduct'])->name('add-product');
    Route::post('/products', [AdminController::class, 'storeProduct'])->name('store-product');
    Route::get('/products/{id}/edit', [AdminController::class, 'editProduct'])->name('edit-product');
    Route::put('/products/{id}', [AdminController::class, 'updateProduct'])->name('update-product');
    Route::delete('/products/{id}', [AdminController::class, 'deleteProduct'])->name('delete-product');
    Route::get('/orders', [AdminController::class, 'allOrders'])->name('all-orders');
    Route::put('/orders/{id}/status', [AdminController::class, 'updateOrderStatus'])->name('update-order-status');
    Route::get('/earning', [AdminController::class, 'earning'])->name('earning');
});
