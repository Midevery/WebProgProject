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
        
        $ordersInProgress = Order::where('user_id', $user->id)
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->count();
        
        $totalSpending = Order::where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');
        
        $rewardPoints = 350;
        
        $activeVouchers = 2;
        
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
        
        $orderStats = Order::where('user_id', $user->id)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        $ongoingCount = ($orderStats['pending'] ?? 0) + ($orderStats['processing'] ?? 0) + ($orderStats['shipped'] ?? 0);
        $cancelledCount = $orderStats['cancelled'] ?? 0;
        $deliveredCount = $orderStats['delivered'] ?? 0;
        
        $recentOrders = Order::where('user_id', $user->id)
            ->with(['orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        if (request()->expectsJson()) {
            return response()->json([
                'ordersInProgress' => $ordersInProgress,
                'totalSpending' => $totalSpending,
                'rewardPoints' => $rewardPoints,
                'activeVouchers' => $activeVouchers,
                'recommendedProducts' => $recommendedProducts,
                'ongoingCount' => $ongoingCount,
                'cancelledCount' => $cancelledCount,
                'deliveredCount' => $deliveredCount,
                'recentOrders' => $recentOrders,
                'wishlistProductIds' => $wishlistProductIds,
            ]);
        }
        
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

    public function apiIndex(Request $request)
    {
        $request->headers->set('Accept', 'application/json');
        return $this->index();
    }
}
