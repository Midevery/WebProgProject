<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Shipping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    private function shippingOptions(): array
    {
        return [
            'fast' => [
                'label' => 'Fast (1-2 days)',
                'price' => 20000,
                'eta' => 'Estimated 1-2 days',
            ],
            'regular' => [
                'label' => 'Regular (3-5 days)',
                'price' => 15000,
                'eta' => 'Estimated 3-5 days',
            ],
            'economy' => [
                'label' => 'Hemat (5-7 days)',
                'price' => 10000,
                'eta' => 'Estimated 5-7 days',
            ],
        ];
    }

    private function ensureNotSeller()
    {
        if (Auth::user()->isSeller()) {
            return redirect()->route('home')->with('error', 'Sellers cannot purchase products.');
        }

        return null;
    }

    private function sanitizeSelection(Request $request): array
    {
        return collect($request->input('selected', []))
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values()
            ->toArray();
    }

    private function fetchSelectedCarts(array $selectedCartIds = [])
    {
        $query = Auth::user()->carts()->with('product');
        if (!empty($selectedCartIds)) {
            $query->whereIn('id', $selectedCartIds);
        }
        return $query->get();
    }

    public function index(Request $request)
    {
        if ($redirect = $this->ensureNotSeller()) {
            return $redirect;
        }

        $selectedCartIds = $this->sanitizeSelection($request);
        $carts = $this->fetchSelectedCarts($selectedCartIds);
        
        if ($carts->isEmpty()) {
            if (!empty($selectedCartIds)) {
                return redirect()->route('cart.index')->with('error', 'Selected cart items were not found or already processed.');
            }

            $carts = $this->fetchSelectedCarts();
        }
        
        if ($carts->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty!');
        }

        if (empty($selectedCartIds)) {
            $selectedCartIds = $carts->pluck('id')->toArray();
        }

        $shippingOptions = $this->shippingOptions();
        $currentShipping = $request->input('shipping_method', 'fast');
        if (!array_key_exists($currentShipping, $shippingOptions)) {
            $currentShipping = 'fast';
        }
        $shippingPrice = $shippingOptions[$currentShipping]['price'];

        $subtotal = $carts->sum(function($cart) {
            return $cart->product->price * $cart->quantity;
        });
        
        $adminFee = 2000;
        $taxRate = 0.1;
        $ppn = $subtotal * $taxRate;
        $total = $subtotal + $adminFee + $ppn + $shippingPrice;

        $isPartialSelection = count($selectedCartIds) < Auth::user()->carts()->count();

        if ($request->expectsJson()) {
            return response()->json([
                'carts' => $carts,
                'subtotal' => $subtotal,
                'adminFee' => $adminFee,
                'ppn' => $ppn,
                'total' => $total,
                'selectedCartIds' => $selectedCartIds,
                'isPartialSelection' => $isPartialSelection,
                'shippingOptions' => $shippingOptions,
                'currentShipping' => $currentShipping,
                'shippingPrice' => $shippingPrice,
                'taxRate' => $taxRate,
            ]);
        }

        return view('payment.index', compact(
            'carts',
            'subtotal',
            'adminFee',
            'ppn',
            'total',
            'selectedCartIds',
            'isPartialSelection',
            'shippingOptions',
            'currentShipping',
            'shippingPrice',
            'taxRate'
        ));
    }

    public function checkout(Request $request)
    {
        if ($redirect = $this->ensureNotSeller()) {
            return $redirect;
        }

        $request->validate([
            'shipping_method' => 'required|string',
            'payment_method' => 'required|in:transfer,cash',
        ]);

        $selectedCartIds = $this->sanitizeSelection($request);
        $carts = $this->fetchSelectedCarts($selectedCartIds);
        
        if ($carts->isEmpty()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Please select at least one item to purchase.',
                ], 422);
            }

            return redirect()->route('cart.index')->with('error', 'Please select at least one item to purchase.');
        }

        if (empty($selectedCartIds)) {
            $selectedCartIds = $carts->pluck('id')->toArray();
        }
        
        $shippingOptions = $this->shippingOptions();
        $shippingMethod = array_key_exists($request->shipping_method, $shippingOptions)
            ? $request->shipping_method
            : 'fast';
        $shippingPrice = $shippingOptions[$shippingMethod]['price'];

        $subtotal = $carts->sum(function($cart) {
            return $cart->product->price * $cart->quantity;
        });
        
        $adminFee = 2000;
        $taxRate = 0.1;
        $ppn = $subtotal * $taxRate;
        $voucher = $request->voucher ? 20000 : 0;
        $total = $subtotal + $adminFee + $ppn + $shippingPrice - $voucher;

        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'user_id' => Auth::id(),
            'total_amount' => $total,
            'status' => 'pending',
            'shipping_address' => Auth::user()->address ?? 'Address not set',
            'shipping_method' => $shippingMethod,
        ]);

        foreach ($carts as $cart) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $cart->product_id,
                'quantity' => $cart->quantity,
                'price' => $cart->product->price,
                'subtotal' => $cart->product->price * $cart->quantity,
            ]);
        }

        $paymentMethod = $request->payment_method === 'transfer' ? 'va' : 'cash';
        $payment = Payment::create([
            'order_id' => $order->id,
            'method' => $paymentMethod,
            'status' => 'pending',
            'amount' => $total,
        ]);

        $firstProduct = $carts->first()->product;
        $sellerId = $firstProduct->seller_id ?? null;

        Shipping::create([
            'order_id' => $order->id,
            'seller_id' => $sellerId,
            'status' => 'pending',
            'courier' => 'JNE - ' . ucfirst($shippingMethod),
        ]);

        session()->put("order_cart_selection.{$order->id}", $selectedCartIds);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Order created',
                'order' => $order->load(['orderItems.product', 'payment', 'shipping']),
            ], 201);
        }

        return redirect()->route('payment.show', $order->id);
    }

    public function show($orderId)
    {
        $order = Order::with(['orderItems.product', 'payment', 'shipping'])
            ->where('user_id', Auth::id())
            ->findOrFail($orderId);

        $shippingOptions = $this->shippingOptions();
        $shippingKey = $order->shipping_method ?? 'fast';
        if (!array_key_exists($shippingKey, $shippingOptions)) {
            $shippingKey = 'fast';
        }
        $shippingDetails = $shippingOptions[$shippingKey];
        $shippingPrice = $shippingDetails['price'];

        $subtotal = $order->orderItems->sum('subtotal');
        $adminFee = 2000;
        $taxRate = 0.1;
        $taxAmount = $subtotal * $taxRate;
        $voucherAmount = 0;
        $calculatedTotal = $subtotal + $adminFee + $taxAmount + $shippingPrice - $voucherAmount;

        if (request()->expectsJson()) {
            return response()->json([
                'order' => $order,
                'shippingDetails' => $shippingDetails,
                'shippingPrice' => $shippingPrice,
                'subtotal' => $subtotal,
                'adminFee' => $adminFee,
                'taxAmount' => $taxAmount,
                'taxRate' => $taxRate,
                'voucherAmount' => $voucherAmount,
                'calculatedTotal' => $calculatedTotal,
            ]);
        }

        return view('payment.show', compact(
            'order',
            'shippingDetails',
            'shippingPrice',
            'subtotal',
            'adminFee',
            'taxAmount',
            'taxRate',
            'voucherAmount',
            'calculatedTotal'
        ));
    }

    public function process(Request $request, $orderId)
    {
        $request->validate([
            'payment_method' => 'required|in:transfer,cash',
        ]);

        $order = Order::where('user_id', Auth::id())->findOrFail($orderId);
        $payment = $order->payment;

        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
            'transaction_id' => 'TXN-' . strtoupper(Str::random(10)),
        ]);

        $order->update(['status' => 'processing']);
        
        $selectionKey = "order_cart_selection.{$order->id}";
        $selectedCartIds = session()->pull($selectionKey, []);

        if (!empty($selectedCartIds)) {
            Auth::user()->carts()->whereIn('id', $selectedCartIds)->delete();
        } else {
            Auth::user()->carts()->delete();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Payment successful!',
                'order' => $order->fresh()->load(['payment', 'shipping', 'orderItems.product']),
            ]);
        }

        return redirect()->route('shipping.tracking', $order->id)->with('success', 'Payment successful!');
    }

    public function apiPreview(Request $request)
    {
        $request->headers->set('Accept', 'application/json');
        return $this->index($request);
    }

    public function apiCheckout(Request $request)
    {
        $request->headers->set('Accept', 'application/json');
        return $this->checkout($request);
    }

    public function apiShow(Request $request, $orderId)
    {
        $request->headers->set('Accept', 'application/json');
        return $this->show($orderId);
    }

    public function apiProcess(Request $request, $orderId)
    {
        $request->headers->set('Accept', 'application/json');
        return $this->process($request, $orderId);
    }
}
