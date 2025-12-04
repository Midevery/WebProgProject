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
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('signin');
        }
        
        $artist = Auth::user();
        
        if (!$artist->isArtist()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Access denied'], 403);
            }
            return redirect()->route('home')->with('error', 'Access denied');
        }

        $products = Product::where('artist_id', $artist->id)
            ->with(['category', 'orderItems.order'])
            ->get();

        $platformFee = 10000;
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
        
        $totalItemsSold = OrderItem::whereHas('product', function($query) use ($artist) {
            $query->where('artist_id', $artist->id);
        })
        ->whereHas('order', function($query) {
            $query->where('status', '!=', 'cancelled');
        })
        ->sum('quantity');
        
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

        $platformFee = 10000;
        foreach ($productsWithSales as $product) {
            $product->total_earning = $product->orderItems()
                ->whereHas('order', function($q) {
                    $q->where('status', '!=', 'cancelled');
                })
                ->sum('subtotal');
            
            $totalItemsSold = $product->total_sold ?? 0;
            $productCost = $product->cost ?? 0;
            $totalCost = $totalItemsSold * $productCost;
            $product->platform_fee = $totalItemsSold * $platformFee;
            $product->net_earning = $product->total_earning - $totalCost - $product->platform_fee;
        }

        if (request()->expectsJson()) {
            return response()->json([
                'products' => $products,
                'totalSales' => $totalSales,
                'totalOrders' => $totalOrders,
                'productsWithSales' => $productsWithSales,
                'artist' => $artist,
                'totalPlatformFee' => $totalPlatformFee,
                'netSales' => $netSales,
            ]);
        }

        return view('artist.dashboard', compact('products', 'totalSales', 'totalOrders', 'productsWithSales', 'artist', 'totalPlatformFee', 'netSales'));
    }

    public function profile()
    {
        if (!Auth::check()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('signin');
        }
        
        $artist = Auth::user();
        
        if (!$artist->isArtist()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Access denied'], 403);
            }
            return redirect()->route('home')->with('error', 'Access denied');
        }

        $products = Product::where('artist_id', $artist->id)
            ->with(['category'])
            ->orderBy('created_at', 'desc')
            ->get();

        $platformFee = 10000;
        foreach ($products as $product) {
            $product->total_sold = OrderItem::where('product_id', $product->id)
                ->whereHas('order', function($q) {
                    $q->where('status', '!=', 'cancelled');
                })
                ->sum('quantity');
            
            $product->total_earning = OrderItem::where('product_id', $product->id)
                ->whereHas('order', function($q) {
                    $q->where('status', '!=', 'cancelled');
                })
                ->sum('subtotal');
            
            $productCost = $product->cost ?? 0;
            $totalCost = $product->total_sold * $productCost;
            $product->platform_fee = $product->total_sold * $platformFee;
            $product->net_earning = $product->total_earning - $totalCost - $product->platform_fee;
            
            $product->total_orders = OrderItem::where('product_id', $product->id)
                ->whereHas('order', function($q) {
                    $q->where('status', '!=', 'cancelled');
                })
                ->distinct('order_id')
                ->count('order_id');
        }

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

        if (request()->expectsJson()) {
            return response()->json([
                'artist' => $artist,
                'products' => $products,
                'totalSales' => $totalSales,
                'totalOrders' => $totalOrders,
            ]);
        }

        return view('artist.profile', compact('artist', 'products', 'totalSales', 'totalOrders'));
    }

    public function productAnalytics($productId)
    {
        if (!Auth::check()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('signin');
        }
        
        $artist = Auth::user();
        
        if (!$artist->isArtist()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Access denied'], 403);
            }
            return redirect()->route('home')->with('error', 'Access denied');
        }

        $product = Product::where('id', $productId)
            ->where('artist_id', $artist->id)
            ->with(['category', 'artist'])
            ->firstOrFail();

        $orderItems = OrderItem::where('product_id', $product->id)
            ->with(['order.user'])
            ->whereHas('order', function($q) {
                $q->where('status', '!=', 'cancelled');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $totalSold = $orderItems->sum('quantity');
        $totalEarning = $orderItems->sum('subtotal');
        $totalOrders = $orderItems->pluck('order_id')->unique()->count();
        
        $platformFee = 10000;
        $productCost = $product->cost ?? 0;
        $totalCost = $totalSold * $productCost;
        $totalPlatformFee = $totalSold * $platformFee;
        $netEarning = $totalEarning - $totalCost - $totalPlatformFee;
        
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

        $conversionRate = $product->clicks > 0 
            ? ($totalOrders / $product->clicks) * 100 
            : 0;

        $avgOrderValue = $totalOrders > 0 
            ? $totalEarning / $totalOrders 
            : 0;

        $recentOrders = $orderItems->take(10);

        if (request()->expectsJson()) {
            return response()->json([
                'product' => $product,
                'orderItems' => $orderItems,
                'totalSold' => $totalSold,
                'totalEarning' => $totalEarning,
                'totalOrders' => $totalOrders,
                'salesByDate' => $salesByDate,
                'salesByMonth' => $salesByMonth,
                'conversionRate' => $conversionRate,
                'avgOrderValue' => $avgOrderValue,
                'recentOrders' => $recentOrders,
                'artist' => $artist,
                'platformFee' => $platformFee,
                'totalPlatformFee' => $totalPlatformFee,
                'netEarning' => $netEarning,
                'totalCost' => $totalCost,
                'productCost' => $productCost,
            ]);
        }

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
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('signin');
        }
        
        $artist = Auth::user();
        
        if (!$artist->isArtist()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Access denied'], 403);
            }
            return redirect()->route('home')->with('error', 'Access denied');
        }

        $product = Product::where('id', $productId)
            ->where('artist_id', $artist->id)
            ->with(['category', 'orderItems.order', 'comments.user'])
            ->firstOrFail();

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

        if (request()->expectsJson()) {
            return response()->json([
                'product' => $product,
                'totalSold' => $totalSold,
                'totalEarning' => $totalEarning,
                'totalBuyers' => $totalBuyers,
                'salesOverTime' => $salesOverTime,
                'artist' => $artist,
            ]);
        }

        return view('artist.item-detail', compact('product', 'totalSold', 'totalEarning', 'totalBuyers', 'salesOverTime', 'artist'));
    }

    public function apiDashboard(Request $request)
    {
        $request->headers->set('Accept', 'application/json');
        return $this->dashboard();
    }

    public function apiProfile(Request $request)
    {
        $request->headers->set('Accept', 'application/json');
        return $this->profile();
    }

    public function apiProductAnalytics(Request $request, $productId)
    {
        $request->headers->set('Accept', 'application/json');
        return $this->productAnalytics($productId);
    }
}
