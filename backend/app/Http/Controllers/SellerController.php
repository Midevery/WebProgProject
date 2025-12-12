<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SellerController extends Controller
{
    public function show($id)
    {
        try {
            $seller = User::where('role', 'seller')
                ->with(['products' => function($query) {
                    $query->with('category')->orderBy('created_at', 'desc');
                }])
                ->findOrFail($id);
            
            return response()->json(['seller' => $seller]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Seller not found');
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
        
        $seller = Auth::user();
        
        if (!$seller->isSeller()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Access denied'], 403);
            }
            return redirect()->route('home')->with('error', 'Access denied');
        }

        $products = Product::where('seller_id', $seller->id)
            ->with(['category', 'orderItems.order'])
            ->get();

        $platformFee = 10000;
        $totalSales = OrderItem::whereHas('product', function($query) use ($seller) {
            $query->where('seller_id', $seller->id);
        })
        ->whereHas('order', function($query) {
            $query->where('status', '!=', 'cancelled');
        })
        ->sum('subtotal');

        $totalOrders = Order::whereHas('orderItems.product', function($query) use ($seller) {
            $query->where('seller_id', $seller->id);
        })
        ->where('status', '!=', 'cancelled')
        ->count();
        
        $totalItemsSold = OrderItem::whereHas('product', function($query) use ($seller) {
            $query->where('seller_id', $seller->id);
        })
        ->whereHas('order', function($query) {
            $query->where('status', '!=', 'cancelled');
        })
        ->sum('quantity');
        
        $totalCost = OrderItem::whereHas('product', function($query) use ($seller) {
            $query->where('seller_id', $seller->id);
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

        $allProducts = Product::where('seller_id', $seller->id)
            ->with(['category'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $productsWithSales = $allProducts->map(function($product) use ($platformFee) {
            $product->total_sold = $product->orderItems()
                ->whereHas('order', function($q) {
                    $q->where('status', '!=', 'cancelled');
                })
                ->sum('quantity');
            
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
            
            return $product;
        })->values();

        if (request()->expectsJson()) {
            return response()->json([
                'products' => $products,
                'totalSales' => $totalSales,
                'totalOrders' => $totalOrders,
                'productsWithSales' => $productsWithSales,
                'seller' => $seller,
                'totalPlatformFee' => $totalPlatformFee,
                'netSales' => $netSales,
            ]);
        }

        return response()->json([
            'products' => $products,
            'totalSales' => $totalSales,
            'totalOrders' => $totalOrders,
            'productsWithSales' => $productsWithSales,
            'seller' => $seller,
            'totalPlatformFee' => $totalPlatformFee,
            'netSales' => $netSales,
        ]);
    }

    public function profile()
    {
        if (!Auth::check()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('signin');
        }
        
        $seller = Auth::user();
        
        if (!$seller->isSeller()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Access denied'], 403);
            }
            return redirect()->route('home')->with('error', 'Access denied');
        }

        $products = Product::where('seller_id', $seller->id)
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

        $totalSales = OrderItem::whereHas('product', function($query) use ($seller) {
            $query->where('seller_id', $seller->id);
        })
        ->whereHas('order', function($query) {
            $query->where('status', '!=', 'cancelled');
        })
        ->sum('subtotal');

        $totalOrders = OrderItem::whereHas('product', function($query) use ($seller) {
            $query->where('seller_id', $seller->id);
        })
        ->whereHas('order', function($query) {
            $query->where('status', '!=', 'cancelled');
        })
        ->distinct('order_id')
        ->count('order_id');

        if (request()->expectsJson()) {
            return response()->json([
                'seller' => $seller,
                'products' => $products,
                'totalSales' => $totalSales,
                'totalOrders' => $totalOrders,
            ]);
        }

        // Using JSX frontend - return JSON
        return response()->json([
            'seller' => $seller,
            'products' => $products,
            'totalSales' => $totalSales,
            'totalOrders' => $totalOrders,
        ]);
    }

    public function addProduct()
    {
        if (!Auth::check()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('signin');
        }
        
        $seller = Auth::user();
        
        if (!$seller->isSeller()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Access denied'], 403);
            }
            return redirect()->route('home')->with('error', 'Access denied');
        }
        
        $categories = Category::all();

        if (request()->expectsJson()) {
            return response()->json([
                'categories' => $categories,
            ]);
        }

        return response()->json(['categories' => $categories]);
    }

    public function storeProduct(Request $request)
    {
        if (!Auth::check()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('signin');
        }
        
        $seller = Auth::user();
        
        if (!$seller->isSeller()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Access denied'], 403);
            }
            return redirect()->route('home')->with('error', 'Access denied');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->cost = $request->cost;
        $product->stock = $request->stock;
        $product->category_id = $request->category_id;
        $product->seller_id = $seller->id;
        $product->clicks = 0;
        $product->sales_count = 0;

        $uploadPath = public_path('images/products');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            
            if ($image->move($uploadPath, $imageName)) {
                $product->image = 'images/products/' . $imageName;
            } else {
                return redirect()->back()->with('error', 'Failed to upload image. Please try again.')->withInput();
            }
        } else {
            return redirect()->back()->with('error', 'Product image is required.')->withInput();
        }

        $product->save();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Product added successfully!',
                'product' => $product,
            ]);
        }

        return redirect()->route('seller.dashboard')->with('success', 'Product added successfully!');
    }

    public function editProduct($id)
    {
        if (!Auth::check()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('signin');
        }
        
        $seller = Auth::user();
        
        if (!$seller->isSeller()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Access denied'], 403);
            }
            return redirect()->route('home')->with('error', 'Access denied');
        }
        
        $product = Product::where('id', $id)
            ->where('seller_id', $seller->id)
            ->firstOrFail();
        $categories = Category::all();

        if (request()->expectsJson()) {
            return response()->json([
                'product' => $product,
                'categories' => $categories,
            ]);
        }

        return response()->json([
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    public function updateProduct(Request $request, $id)
    {
        if (!Auth::check()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('signin');
        }
        
        $seller = Auth::user();
        
        if (!$seller->isSeller()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Access denied'], 403);
            }
            return redirect()->route('home')->with('error', 'Access denied');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $product = Product::where('id', $id)
            ->where('seller_id', $seller->id)
            ->firstOrFail();
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->cost = $request->cost;
        $product->stock = $request->stock;
        $product->category_id = $request->category_id;

        $uploadPath = public_path('images/products');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            
            if ($image->move($uploadPath, $imageName)) {
                if ($product->image && file_exists(public_path($product->image))) {
                    @unlink(public_path($product->image));
                }
                
                $product->image = 'images/products/' . $imageName;
            } else {
                return redirect()->back()->with('error', 'Failed to upload image. Please try again.')->withInput();
            }
        }

        $product->save();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Product updated successfully!',
                'product' => $product,
            ]);
        }

        return redirect()->route('seller.dashboard')->with('success', 'Product updated successfully!');
    }

    public function deleteProduct($id)
    {
        if (!Auth::check()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('signin');
        }
        
        $seller = Auth::user();
        
        if (!$seller->isSeller()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Access denied'], 403);
            }
            return redirect()->route('home')->with('error', 'Access denied');
        }
        
        $product = Product::where('id', $id)
            ->where('seller_id', $seller->id)
            ->firstOrFail();
        $product->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Product deleted successfully!',
            ]);
        }

        return redirect()->route('seller.dashboard')->with('success', 'Product deleted successfully!');
    }

    public function productAnalytics($productId)
    {
        if (!Auth::check()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('signin');
        }
        
        $seller = Auth::user();
        
        if (!$seller->isSeller()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Access denied'], 403);
            }
            return redirect()->route('home')->with('error', 'Access denied');
        }

        $product = Product::where('id', $productId)
            ->where('seller_id', $seller->id)
            ->with(['category', 'seller'])
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
                'seller' => $seller,
                'platformFee' => $platformFee,
                'totalPlatformFee' => $totalPlatformFee,
                'netEarning' => $netEarning,
                'totalCost' => $totalCost,
                'productCost' => $productCost,
            ]);
        }

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
            'seller' => $seller,
            'platformFee' => $platformFee,
            'totalPlatformFee' => $totalPlatformFee,
            'netEarning' => $netEarning,
            'totalCost' => $totalCost,
            'productCost' => $productCost,
        ]);
    }

    public function itemDetail($productId)
    {
        if (!Auth::check()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('signin');
        }
        
        $seller = Auth::user();
        
        if (!$seller->isSeller()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Access denied'], 403);
            }
            return redirect()->route('home')->with('error', 'Access denied');
        }

        $product = Product::where('id', $productId)
            ->where('seller_id', $seller->id)
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
                'seller' => $seller,
            ]);
        }

        return response()->json([
            'product' => $product,
            'totalSold' => $totalSold,
            'totalEarning' => $totalEarning,
            'totalBuyers' => $totalBuyers,
            'salesOverTime' => $salesOverTime,
            'seller' => $seller,
        ]);
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

