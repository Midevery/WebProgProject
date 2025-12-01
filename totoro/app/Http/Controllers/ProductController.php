<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'artist']);

        // Filter by availability
        if ($request->has('availability')) {
            if ($request->availability === 'ready') {
                $query->where('stock', '>', 0);
            } elseif ($request->availability === 'preorder') {
                $query->where('stock', '<=', 0);
            }
        }

        // Filter by price range
        if ($request->has('min_price') && $request->min_price != '') {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price != '') {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by category (character & series)
        $selectedCategories = collect($request->input('categories', []))
            ->filter(fn($id) => $id !== null && $id !== '')
            ->map(fn($id) => (int) $id)
            ->toArray();

        if ($request->filled('category')) {
            $selectedCategories[] = (int) $request->category;
        }

        $selectedCategories = array_unique($selectedCategories);

        if (!empty($selectedCategories)) {
            $query->whereIn('category_id', $selectedCategories);
        }

        // Search by illustrator
        if ($request->has('illustrator') && $request->illustrator) {
            $query->whereHas('artist', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->illustrator . '%');
            });
        }

        // Search by product name
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->paginate(15);
        $categories = Category::all();

        return view('products.index', compact('products', 'categories', 'selectedCategories'));
    }

    public function show($id)
    {
        $product = Product::with(['category', 'artist', 'comments.user'])->findOrFail($id);
        
        // Increment clicks
        $product->increment('clicks');
        
        // Get recently viewed (dummy for now)
        $recentlyViewed = Product::where('id', '!=', $id)->limit(3)->get();
        
        // Get similar products
        $similarProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $id)
            ->limit(3)
            ->get();

        // Check if product is in wishlist
        $inWishlist = false;
        if (Auth::check()) {
            $inWishlist = Auth::user()->wishlists()->where('product_id', $id)->exists();
        }

        return view('products.show', compact('product', 'recentlyViewed', 'similarProducts', 'inWishlist'));
    }
}
