<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ArtistController extends Controller
{
    public function show($id)
    {
        try {
            $artist = User::where('role', 'artist')
                ->with(['products' => function($query) {
                    $query->with('category')->orderBy('created_at', 'desc');
                }])
                ->findOrFail($id);
            
            return view('artist.show', compact('artist'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Artist not found');
        }
    }

    public function dashboard()
    {
        if (!Auth::check()) {
            return redirect()->route('signin');
        }
        
        $artist = Auth::user();
        
        if (!$artist->isArtist()) {
            return redirect()->route('home')->with('error', 'Access denied');
        }

        // Get all products by this artist
        $products = Product::where('artist_id', $artist->id)
            ->with(['category', 'orderItems.order'])
            ->get();

        // Calculate sales statistics
        $platformFee = 10000; // Platform fee per item (fixed)
        $totalSales = OrderItem::whereHas('product', function($query) use ($artist) {
            $query->where('artist_id', $artist->id);
        })
        ->whereHas('order', function($query) {
            $query->where('status', '!=', 'cancelled');
        })
        ->sum('subtotal');

        $totalOrders = OrderItem::whereHas('product', function($query) use ($artist) {
            $query->where('artist_id', $artist->id);
        })
        ->whereHas('order', function($query) {
            $query->where('status', '!=', 'cancelled');
        })
        ->count();
        
        // Calculate total items sold, cost, and platform fee
        $totalItemsSold = OrderItem::whereHas('product', function($query) use ($artist) {
            $query->where('artist_id', $artist->id);
        })
        ->whereHas('order', function($query) {
            $query->where('status', '!=', 'cancelled');
        })
        ->sum('quantity');
        
        // Calculate total cost from all products sold
        $totalCost = OrderItem::whereHas('product', function($query) use ($artist) {
            $query->where('artist_id', $artist->id);
        })
        ->whereHas('order', function($query) {
            $query->where('status', '!=', 'cancelled');
        })
        ->get()
        ->sum(function($item) {
            return ($item->product->cost ?? 0) * $item->quantity;
        });
        
        $totalPlatformFee = $totalItemsSold * $platformFee;
        $netSales = $totalSales - $totalCost - $totalPlatformFee;

        // Get ALL products with sales data - no limit
        $productsWithSales = Product::where('artist_id', $artist->id)
            ->with(['category'])
            ->orderBy('created_at', 'desc')
            ->withCount(['orderItems as total_sold' => function($query) {
                $query->whereHas('order', function($q) {
                    $q->where('status', '!=', 'cancelled');
                });
            }])
            ->withSum(['orderItems as total_earning' => function($query) {
                $query->whereHas('order', function($q) {
                    $q->where('status', '!=', 'cancelled');
                });
            }], 'subtotal')
            ->get();

        // Calculate total earnings per product with cost and platform fee
        $platformFee = 10000; // Platform fee per item (fixed)
        foreach ($productsWithSales as $product) {
            $product->total_earning = $product->orderItems()
                ->whereHas('order', function($q) {
                    $q->where('status', '!=', 'cancelled');
                })
                ->sum('subtotal');
            
            // Calculate net earning (after cost and platform fee)
            $totalItemsSold = $product->total_sold ?? 0;
            $productCost = $product->cost ?? 0;
            $totalCost = $totalItemsSold * $productCost;
            $product->platform_fee = $totalItemsSold * $platformFee;
            $product->net_earning = $product->total_earning - $totalCost - $product->platform_fee;
        }

        return view('artist.dashboard', compact('products', 'totalSales', 'totalOrders', 'productsWithSales', 'artist', 'totalPlatformFee', 'netSales'));
    }

    public function profile()
    {
        if (!Auth::check()) {
            return redirect()->route('signin');
        }
        
        $artist = Auth::user();
        
        if (!$artist->isArtist()) {
            return redirect()->route('home')->with('error', 'Access denied');
        }

        // Get ALL products by this artist with detailed analytics - no limit
        $products = Product::where('artist_id', $artist->id)
            ->with(['category'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate analytics for each product with cost and platform fee
        $platformFee = 10000; // Platform fee per item (fixed)
        foreach ($products as $product) {
            // Total sold
            $product->total_sold = OrderItem::where('product_id', $product->id)
                ->whereHas('order', function($q) {
                    $q->where('status', '!=', 'cancelled');
                })
                ->sum('quantity');
            
            // Total earning
            $product->total_earning = OrderItem::where('product_id', $product->id)
                ->whereHas('order', function($q) {
                    $q->where('status', '!=', 'cancelled');
                })
                ->sum('subtotal');
            
            // Calculate cost, platform fee and net earning
            $productCost = $product->cost ?? 0;
            $totalCost = $product->total_sold * $productCost;
            $product->platform_fee = $product->total_sold * $platformFee;
            $product->net_earning = $product->total_earning - $totalCost - $product->platform_fee;
            
            // Total orders
            $product->total_orders = OrderItem::where('product_id', $product->id)
                ->whereHas('order', function($q) {
                    $q->where('status', '!=', 'cancelled');
                })
                ->distinct('order_id')
                ->count('order_id');
        }

        // Overall statistics
        $totalSales = OrderItem::whereHas('product', function($query) use ($artist) {
            $query->where('artist_id', $artist->id);
        })
        ->whereHas('order', function($query) {
            $query->where('status', '!=', 'cancelled');
        })
        ->sum('subtotal');

        $totalOrders = OrderItem::whereHas('product', function($query) use ($artist) {
            $query->where('artist_id', $artist->id);
        })
        ->whereHas('order', function($query) {
            $query->where('status', '!=', 'cancelled');
        })
        ->distinct('order_id')
        ->count('order_id');

        return view('artist.profile', compact('artist', 'products', 'totalSales', 'totalOrders'));
    }

    public function productAnalytics($productId)
    {
        if (!Auth::check()) {
            return redirect()->route('signin');
        }
        
        $artist = Auth::user();
        
        if (!$artist->isArtist()) {
            return redirect()->route('home')->with('error', 'Access denied');
        }

        // Get product and verify it belongs to this artist
        $product = Product::where('id', $productId)
            ->where('artist_id', $artist->id)
            ->with(['category', 'artist'])
            ->firstOrFail();

        // Get all order items for this product
        $orderItems = OrderItem::where('product_id', $product->id)
            ->with(['order.user'])
            ->whereHas('order', function($q) {
                $q->where('status', '!=', 'cancelled');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate detailed statistics
        $totalSold = $orderItems->sum('quantity');
        $totalEarning = $orderItems->sum('subtotal');
        $totalOrders = $orderItems->pluck('order_id')->unique()->count();
        
        // Calculate cost, platform fee and net earning
        $platformFee = 10000; // Platform fee per item (fixed)
        $productCost = $product->cost ?? 0;
        $totalCost = $totalSold * $productCost;
        $totalPlatformFee = $totalSold * $platformFee;
        $netEarning = $totalEarning - $totalCost - $totalPlatformFee;
        
        // Sales by date (last 30 days)
        $salesByDate = OrderItem::where('product_id', $product->id)
            ->whereHas('order', function($q) {
                $q->where('status', '!=', 'cancelled');
            })
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(quantity) as quantity_sold'),
                DB::raw('SUM(subtotal) as earning')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Sales by month (last 12 months)
        $salesByMonth = OrderItem::where('product_id', $product->id)
            ->whereHas('order', function($q) {
                $q->where('status', '!=', 'cancelled');
            })
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(quantity) as quantity_sold'),
                DB::raw('SUM(subtotal) as earning')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Conversion rate (views to sales)
        $conversionRate = $product->clicks > 0 
            ? ($totalOrders / $product->clicks) * 100 
            : 0;

        // Average order value
        $avgOrderValue = $totalOrders > 0 
            ? $totalEarning / $totalOrders 
            : 0;

        // Recent orders
        $recentOrders = $orderItems->take(10);

        return view('artist.product-analytics', compact(
            'product', 
            'orderItems', 
            'totalSold', 
            'totalEarning', 
            'totalOrders',
            'salesByDate',
            'salesByMonth',
            'conversionRate',
            'avgOrderValue',
            'recentOrders',
            'artist',
            'platformFee',
            'totalPlatformFee',
            'netEarning',
            'totalCost',
            'productCost'
        ));
    }

    public function itemDetail($productId)
    {
        if (!Auth::check()) {
            return redirect()->route('signin');
        }
        
        $artist = Auth::user();
        
        if (!$artist->isArtist()) {
            return redirect()->route('home')->with('error', 'Access denied');
        }

        $product = Product::where('id', $productId)
            ->where('artist_id', $artist->id)
            ->with(['category', 'orderItems.order', 'comments.user'])
            ->firstOrFail();

        // Product analytics
        $totalSold = $product->orderItems()
            ->whereHas('order', function($q) {
                $q->where('status', '!=', 'cancelled');
            })
            ->sum('quantity');

        $totalEarning = $product->orderItems()
            ->whereHas('order', function($q) {
                $q->where('status', '!=', 'cancelled');
            })
            ->sum('subtotal');

        $totalBuyers = $product->orderItems()
            ->whereHas('order', function($q) {
                $q->where('status', '!=', 'cancelled');
            })
            ->distinct('order_id')
            ->count('order_id');

        // Sales over time (last 7 days)
        $salesOverTime = OrderItem::where('product_id', $product->id)
            ->whereHas('order', function($q) {
                $q->where('status', '!=', 'cancelled')
                  ->where('created_at', '>=', now()->subDays(7));
            })
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(quantity) as sold'),
                DB::raw('SUM(subtotal) as earning')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('artist.item-detail', compact('product', 'totalSold', 'totalEarning', 'totalBuyers', 'salesOverTime', 'artist'));
    }
}
