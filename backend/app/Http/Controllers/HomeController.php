<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
            return redirect()->route('signin');
        }
        
        $user = Auth::user();
        
        if ($user->isSeller()) {
            if (request()->expectsJson()) {
                return response()->json(['redirect' => '/seller/dashboard']);
            }
            return redirect()->route('seller.dashboard');
        }
        
        $categories = Category::all();
        $sellers = User::where('role', 'seller')->limit(6)->get();
        $featuredProducts = Product::with(['category', 'seller'])
            ->orderBy('sales_count', 'desc')
            ->limit(12)
            ->get();
        $trendingProducts = Product::with(['category', 'seller'])
            ->orderBy('clicks', 'desc')
            ->limit(6)
            ->get();
        
        if (request()->expectsJson()) {
            return response()->json([
                'categories' => $categories,
                'sellers' => $sellers,
                'featuredProducts' => $featuredProducts,
                'trendingProducts' => $trendingProducts,
            ]);
        }
        
        return redirect()->route('customer.dashboard');
    }
    
    public function showHome()
    {
        return $this->index();
    }
}
