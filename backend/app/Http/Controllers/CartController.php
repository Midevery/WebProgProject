<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Ensure only customers can interact with cart features.
     */
    private function ensureCustomerAccess(): ?RedirectResponse
    {
        if (!Auth::user()->isCustomer()) {
            return redirect()
                ->route('home')
                ->with('error', 'Cart is available for customer accounts only.');
        }

        return null;
    }

    public function index()
    {
        if ($redirect = $this->ensureCustomerAccess()) {
            return $redirect;
        }

        $carts = Auth::user()
            ->carts()
            ->with(['product.artist'])
            ->latest()
            ->get();

        if (request()->expectsJson()) {
            return response()->json([
                'items' => $carts,
            ]);
        }

        return view('cart.index', compact('carts'));
    }

    public function store(Request $request)
    {
        if ($redirect = $this->ensureCustomerAccess()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Cart is available for customer accounts only.',
                ], 403);
            }
            return $redirect;
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        $availableStock = $product->stock > 0 ? $product->stock : null;

        if ($availableStock !== null && $request->quantity > $availableStock) {
            $message = 'Requested quantity exceeds available stock.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $message);
        }

        $cart = Cart::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->first();

        $newQuantity = $request->quantity;

        if ($cart) {
            $newQuantity = $cart->quantity + $request->quantity;
            if ($availableStock !== null && $newQuantity > $availableStock) {
                $message = 'Adding this quantity exceeds available stock.';

                if ($request->expectsJson()) {
                    return response()->json(['message' => $message], 422);
                }

                return redirect()
                    ->back()
                    ->with('error', $message);
            }
            $cart->quantity = $newQuantity;
            $cart->save();
        } else {
            Cart::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Product added to cart!',
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Product added to cart!');
    }

    public function update(Request $request, $id)
    {
        if ($redirect = $this->ensureCustomerAccess()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Cart is available for customer accounts only.',
                ], 403);
            }
            return $redirect;
        }

        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::where('user_id', Auth::id())
            ->with('product')
            ->findOrFail($id);

        $availableStock = ($cart->product && $cart->product->stock > 0)
            ? $cart->product->stock
            : null;

        if ($availableStock !== null && $request->quantity > $availableStock) {
            $message = "Only {$availableStock} item(s) available in stock.";

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        $cart->quantity = $request->quantity;
        $cart->save();

        if ($request->expectsJson()) {
            $lineTotal = $cart->product->price * $cart->quantity;

            return response()->json([
                'quantity' => $cart->quantity,
                'line_total' => $lineTotal,
                'message' => 'Cart updated successfully.',
            ]);
        }

        return back()->with('success', 'Cart updated!');
    }

    public function destroy($id)
    {
        if (request()->expectsJson() && !Auth::user()->isCustomer()) {
            return response()->json([
                'message' => 'Cart is available for customer accounts only.',
            ], 403);
        }
        if ($redirect = $this->ensureCustomerAccess()) {
            return $redirect;
        }

        $cart = Cart::where('user_id', Auth::id())->findOrFail($id);
        $cart->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Item removed from cart!',
            ]);
        }

        return back()->with('success', 'Item removed from cart!');
    }

    public function apiIndex(Request $request)
    {
        $request->headers->set('Accept', 'application/json');
        return $this->index();
    }

    public function apiStore(Request $request)
    {
        $request->headers->set('Accept', 'application/json');
        return $this->store($request);
    }

    public function apiUpdate(Request $request, $id)
    {
        $request->headers->set('Accept', 'application/json');
        return $this->update($request, $id);
    }

    public function apiDestroy($id)
    {
        request()->headers->set('Accept', 'application/json');
        return $this->destroy($id);
    }
}
