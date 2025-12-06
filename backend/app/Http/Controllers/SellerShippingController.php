<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shipping;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SellerShippingController extends Controller
{
    public function index()
    {
        if (!Auth::check() || !Auth::user()->isSeller()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Access denied'], 403);
            }
            return redirect()->route('home')->with('error', 'Access denied');
        }

        $seller = Auth::user();
        
        $orders = Order::whereHas('orderItems.product', function($query) use ($seller) {
            $query->where('seller_id', $seller->id);
        })
        ->with(['shipping', 'orderItems.product', 'user'])
        ->where('status', '!=', 'cancelled')
        ->orderBy('created_at', 'desc')
        ->get();

        if (request()->expectsJson()) {
            return response()->json([
                'orders' => $orders,
            ]);
        }

        return response()->json([
            'orders' => $orders,
        ]);
    }

    public function updateStatus(Request $request, $orderId)
    {
        if (!Auth::check() || !Auth::user()->isSeller()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Access denied'], 403);
            }
            return redirect()->route('home')->with('error', 'Access denied');
        }

        $seller = Auth::user();
        
        $order = Order::whereHas('orderItems.product', function($query) use ($seller) {
            $query->where('seller_id', $seller->id);
        })
        ->with('shipping')
        ->findOrFail($orderId);

        $request->validate([
            'status' => 'required|in:processing,shipped,delivered',
            'tracking_number' => 'nullable|string|max:255',
            'courier' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $shipping = $order->shipping;
        
        if (!$shipping) {
            $shipping = Shipping::create([
                'order_id' => $order->id,
                'seller_id' => $seller->id,
                'status' => $request->status,
            ]);
        } else {
            $shipping->status = $request->status;
        }
        
        if ($request->filled('tracking_number')) {
            $shipping->tracking_number = $request->tracking_number;
        }
        
        if ($request->filled('courier') && !$shipping->courier) {
            $shipping->courier = $request->courier;
        }
        
        if ($request->filled('notes')) {
            $shipping->notes = $request->notes;
        }

        if ($request->status === 'shipped' && !$shipping->shipped_at) {
            $shipping->shipped_at = now();
            if (!$shipping->tracking_number && $request->filled('tracking_number')) {
                $shipping->tracking_number = $request->tracking_number;
            } elseif (!$shipping->tracking_number) {
                $shipping->tracking_number = 'TRK-' . strtoupper(Str::random(10));
            }
            if ($order->status === 'processing') {
                $order->status = 'shipped';
                $order->save();
            }
        }
        
        if ($request->status === 'processing') {
            if ($order->status === 'pending') {
                $order->status = 'processing';
                $order->save();
            }
        }

        if ($request->status === 'delivered' && !$shipping->delivered_at) {
            $shipping->delivered_at = now();
            if (!$shipping->shipped_at) {
                $shipping->shipped_at = now();
            }
            
            $order->status = 'delivered';
            $order->save();
            
            foreach ($order->orderItems as $item) {
                if ($item->product->seller_id === $seller->id) {
                    $sellerEarning = $item->subtotal * 0.8;
                    $seller->balance += $sellerEarning;
                }
            }
            $seller->save();
        }

        $shipping->save();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Shipping status updated successfully!',
                'shipping' => $shipping->fresh(),
            ]);
        }

        return redirect()->back()->with('success', 'Shipping status updated successfully!');
    }

    public function show($orderId)
    {
        if (!Auth::check() || !Auth::user()->isSeller()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Access denied'], 403);
            }
            return redirect()->route('home')->with('error', 'Access denied');
        }

        $seller = Auth::user();
        
        $order = Order::whereHas('orderItems.product', function($query) use ($seller) {
            $query->where('seller_id', $seller->id);
        })
        ->with(['shipping', 'orderItems.product', 'user'])
        ->findOrFail($orderId);

        if (request()->expectsJson()) {
            return response()->json([
                'order' => $order,
            ]);
        }

        return response()->json([
            'order' => $order,
        ]);
    }
}

