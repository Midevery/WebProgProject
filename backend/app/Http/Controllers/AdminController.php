<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Category;
use App\Models\Shipping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    private function checkAdmin()
    {
        if (!Auth::check()) {
            return redirect()->route('signin');
        }
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Access denied');
        }
        return null;
    }

    public function dashboard()
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }
        
        // Total Orders
        $totalOrders = Order::where('status', '!=', 'cancelled')->count();
        
        // Total Users
        $totalUsers = User::where('role', '!=', 'admin')->count();
        
        // Total Visitors (dummy - bisa diimplementasikan dengan tracking nanti)
        $totalVisitors = 24900; // Placeholder
        
        // Total Sales
        $totalSales = Order::where('status', '!=', 'cancelled')->sum('total_amount');
        
        // Revenue Growth (last 7 days)
        $revenueGrowth = Order::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', now()->subDays(7))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Daily Income (last 7 days)
        $dailyIncome = Order::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', now()->subDays(7))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as income')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Order Status Summary
        $orderStatusSummary = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        // Top Earning Categories
        $topCategories = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->select('categories.name', DB::raw('SUM(order_items.subtotal) as total'))
            ->groupBy('categories.name')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();
        
        // Recent Orders
        $recentOrders = Order::with(['user', 'orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('admin.dashboard', compact(
            'totalOrders',
            'totalUsers',
            'totalVisitors',
            'totalSales',
            'revenueGrowth',
            'dailyIncome',
            'orderStatusSummary',
            'topCategories',
            'recentOrders'
        ));
    }

    public function allProducts(Request $request)
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }
        
        $query = Product::with(['category', 'artist']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('id', 'like', '%' . $search . '%');
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            if ($request->status === 'in_stock') {
                $query->where('stock', '>', 10);
            } elseif ($request->status === 'out_of_stock') {
                $query->where('stock', '<=', 0);
            } elseif ($request->status === 'low_stock') {
                $query->where('stock', '>', 0)->where('stock', '<=', 10);
            }
        }

        // Sort
        $sortBy = $request->get('sort', 'new');
        switch ($sortBy) {
            case 'new':
                $query->orderBy('created_at', 'desc');
                break;
            case 'old':
                $query->orderBy('created_at', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
        }

        $products = $query->paginate(15);

        return view('admin.all-products', compact('products'));
    }

    public function addProduct()
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }
        
        $categories = Category::all();
        $artists = User::where('role', 'artist')->get();
        
        return view('admin.add-product', compact('categories', 'artists'));
    }

    public function storeProduct(Request $request)
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'artist_id' => 'required|exists:users,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->cost = $request->cost;
        $product->stock = $request->stock;
        $product->category_id = $request->category_id;
        $product->artist_id = $request->artist_id;
        $product->clicks = 0;
        $product->sales_count = 0;

        // Create directory if it doesn't exist
        $uploadPath = public_path('images/products');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            
            // Move uploaded file
            if ($image->move($uploadPath, $imageName)) {
                $product->image = 'images/products/' . $imageName;
            } else {
                return redirect()->back()->with('error', 'Failed to upload image. Please try again.')->withInput();
            }
        } else {
            return redirect()->back()->with('error', 'Product image is required.')->withInput();
        }

        $product->save();

        return redirect()->route('admin.all-products')->with('success', 'Product added successfully!');
    }

    public function editProduct($id)
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }
        
        $product = Product::findOrFail($id);
        $categories = Category::all();
        $artists = User::where('role', 'artist')->get();
        
        return view('admin.edit-product', compact('product', 'categories', 'artists'));
    }

    public function updateProduct(Request $request, $id)
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'artist_id' => 'required|exists:users,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $product = Product::findOrFail($id);
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->cost = $request->cost;
        $product->stock = $request->stock;
        $product->category_id = $request->category_id;
        $product->artist_id = $request->artist_id;

        // Create directory if it doesn't exist
        $uploadPath = public_path('images/products');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            
            // Move uploaded file
            if ($image->move($uploadPath, $imageName)) {
                // Delete old image if exists
                if ($product->image && file_exists(public_path($product->image))) {
                    @unlink(public_path($product->image));
                }
                
                $product->image = 'images/products/' . $imageName;
            } else {
                return redirect()->back()->with('error', 'Failed to upload image. Please try again.')->withInput();
            }
        }

        $product->save();

        return redirect()->route('admin.all-products')->with('success', 'Product updated successfully!');
    }

    public function deleteProduct($id)
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }
        
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.all-products')->with('success', 'Product deleted successfully!');
    }

    public function allOrders(Request $request)
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }
        
        $query = Order::with(['user', 'orderItems.product']);

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', '%' . $search . '%')
                  ->orWhere('id', 'like', '%' . $search . '%')
                  ->orWhere('status', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($subQuery) use ($search) {
                      $subQuery->where('name', 'like', '%' . $search . '%')
                               ->orWhere('username', 'like', '%' . $search . '%')
                               ->orWhere('email', 'like', '%' . $search . '%');
                  });
            });
        }

        // Sort
        $sortBy = $request->get('sort', 'new');
        switch ($sortBy) {
            case 'new':
                $query->orderBy('created_at', 'desc');
                break;
            case 'old':
                $query->orderBy('created_at', 'asc');
                break;
        }

        $orders = $query->paginate(15);

        // Count by status
        $orderCounts = [
            'all' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        return view('admin.all-orders', compact('orders', 'orderCounts'));
    }

    public function updateOrderStatus(Request $request, $id)
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }
        
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order = Order::findOrFail($id);
        $oldStatus = $order->status;
        $order->status = $request->status;
        $order->save();

        // Update shipping status
        $shipping = $order->shipping;
        if ($shipping) {
            if ($request->status === 'shipped') {
                $shipping->status = 'shipped';
                $shipping->shipped_at = now();
                if (!$shipping->tracking_number) {
                    $shipping->tracking_number = 'TRK-' . strtoupper(Str::random(10));
                }
            } elseif ($request->status === 'delivered') {
                $shipping->status = 'delivered';
                $shipping->delivered_at = now();
                if (!$shipping->shipped_at) {
                    $shipping->shipped_at = now();
                }
                
                // Update seller balance when order is delivered
                if ($oldStatus !== 'delivered') {
                    foreach ($order->orderItems as $item) {
                        $product = $item->product;
                        if ($product && $product->artist_id) {
                            $artist = User::find($product->artist_id);
                            if ($artist) {
                                // Calculate artist earning (80% of product price, adjust as needed)
                                $artistEarning = $item->subtotal * 0.8;
                                $artist->balance += $artistEarning;
                                $artist->save();
                            }
                        }
                    }
                }
            } elseif ($request->status === 'processing') {
                $shipping->status = 'processing';
            } elseif ($request->status === 'cancelled') {
                $shipping->status = 'pending';
            }
            $shipping->save();
        }

        return redirect()->back()->with('success', 'Order status updated successfully!');
    }

    public function earning()
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }
        
        // Total Revenue (Gross)
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total_amount');
        
        // Calculate admin net earning (cost + platform fee)
        $platformFee = 10000; // Platform fee per item (fixed)
        $totalItemsSold = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->sum('order_items.quantity');
        
        $totalCost = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->get()
            ->sum(function($item) {
                return ($item->product->cost ?? 0) * $item->quantity;
            });
        
        $totalPlatformFee = $totalItemsSold * $platformFee;
        $adminNetEarning = $totalCost + $totalPlatformFee; // Admin gets cost + platform fee
        
        // Earnings by Product
        $productEarnings = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->select(
                'products.id',
                'products.name',
                'products.image',
                'products.price',
                'products.cost',
                DB::raw('SUM(order_items.quantity) as total_sales'),
                DB::raw('SUM(order_items.subtotal) as total_earning')
            )
            ->groupBy('products.id', 'products.name', 'products.image', 'products.price', 'products.cost')
            ->orderBy('total_earning', 'desc')
            ->get();
        
        // Calculate net earning per product for admin
        foreach ($productEarnings as $product) {
            $productCost = $product->cost ?? 0;
            $product->total_cost = $product->total_sales * $productCost;
            $product->platform_fee = $product->total_sales * $platformFee;
            $product->admin_net_earning = $product->total_cost + $product->platform_fee;
        }
        
        // Earnings by Category
        $categoryEarnings = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->select(
                'categories.name',
                DB::raw('SUM(order_items.subtotal) as total_earning')
            )
            ->groupBy('categories.name')
            ->orderBy('total_earning', 'desc')
            ->get();
        
        // Earnings by Artist
        $artistEarnings = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->join('users', 'products.artist_id', '=', 'users.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->select(
                'users.id',
                'users.name',
                'users.username',
                DB::raw('SUM(order_items.subtotal) as total_earning')
            )
            ->groupBy('users.id', 'users.name', 'users.username')
            ->orderBy('total_earning', 'desc')
            ->get();

        return view('admin.earning', compact(
            'totalRevenue', 
            'productEarnings', 
            'categoryEarnings', 
            'artistEarnings',
            'totalCost',
            'totalPlatformFee',
            'adminNetEarning',
            'platformFee'
        ));
    }

    public function profile()
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }
        
        $admin = Auth::user();
        
        // Analytics for admin
        $totalProductsSold = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->sum('order_items.quantity');
        
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total_amount');
        
        $totalOrders = Order::where('status', '!=', 'cancelled')->count();
        
        $totalUsers = User::where('role', '!=', 'admin')->count();
        
        $totalArtists = User::where('role', 'artist')->count();
        
        $totalProducts = Product::count();
        
        // Recent sales
        $recentSales = Order::with(['user', 'orderItems.product'])
            ->where('status', '!=', 'cancelled')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('admin.profile', compact('admin', 'totalProductsSold', 'totalRevenue', 'totalOrders', 'totalUsers', 'totalArtists', 'totalProducts', 'recentSales'));
    }
}

