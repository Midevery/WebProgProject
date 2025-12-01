<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Order in Progress (orders that are not delivered or cancelled)
        $ordersInProgress = Order::where('user_id', $user->id)
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->count();
        
        // Total Spending This Month
        $totalSpending = Order::where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');
        
        // Reward Points (dummy - bisa diimplementasikan nanti)
        $rewardPoints = 350; // Placeholder
        
        // Active Vouchers (dummy - bisa diimplementasikan nanti)
        $activeVouchers = 2; // Placeholder
        
        // Recommended Products (products user hasn't bought yet)
        $boughtProductIds = Order::where('user_id', $user->id)
            ->with('orderItems')
            ->get()
            ->pluck('orderItems')
            ->flatten()
            ->pluck('product_id')
            ->unique()
            ->toArray();
        
        $recommendedProducts = Product::with(['category', 'artist'])
            ->whereNotIn('id', $boughtProductIds)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        $wishlistProductIds = $user->wishlists()
            ->pluck('product_id')
            ->toArray();
        
        // Order Statistics
        $orderStats = Order::where('user_id', $user->id)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        $ongoingCount = ($orderStats['pending'] ?? 0) + ($orderStats['processing'] ?? 0) + ($orderStats['shipped'] ?? 0);
        $cancelledCount = $orderStats['cancelled'] ?? 0;
        $deliveredCount = $orderStats['delivered'] ?? 0;
        
        // Recent Orders
        $recentOrders = Order::where('user_id', $user->id)
            ->with(['orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();
        
        return view('customer.dashboard', compact(
            'ordersInProgress',
            'totalSpending',
            'rewardPoints',
            'activeVouchers',
            'recommendedProducts',
            'ongoingCount',
            'cancelledCount',
            'deliveredCount',
            'recentOrders',
            'wishlistProductIds'
        ));
    }
}
