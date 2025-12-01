<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShippingController extends Controller
{
    public function index()
    {
        $orders = Order::with('shipping')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('shipping.index', compact('orders'));
    }

    public function tracking($orderId)
    {
        $order = Order::with(['shipping', 'orderItems.product'])
            ->where('user_id', Auth::id())
            ->findOrFail($orderId);
        
        return view('shipping.tracking', compact('order'));
    }
}
