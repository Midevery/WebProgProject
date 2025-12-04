<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    private function customerOnlyRedirect()
    {
        if (!Auth::user()->isCustomer()) {
            return redirect()
                ->route('home')
                ->with('error', 'Wishlist is available for customer accounts only.');
        }

        return null;
    }

    public function index()
    {
        if ($redirect = $this->customerOnlyRedirect()) {
            return $redirect;
        }

        $wishlists = Auth::user()->wishlists()->with('product')->get();

        if (request()->expectsJson()) {
            return response()->json([
                'items' => $wishlists,
            ]);
        }

        return view('wishlist.index', compact('wishlists'));
    }

    public function store(Request $request)
    {
        if ($redirect = $this->customerOnlyRedirect()) {
            return $redirect;
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $wishlist = Wishlist::firstOrCreate([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
        ]);

        if ($wishlist->wasRecentlyCreated) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'added',
                    'message' => 'Product added to wishlist!',
                ]);
            }
            return back()->with('success', 'Product added to wishlist!');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'exists',
                'message' => 'Product already in wishlist!',
            ]);
        }

        return back()->with('info', 'Product already in wishlist!');
    }

    public function destroy($id)
    {
        if ($redirect = $this->customerOnlyRedirect()) {
            return $redirect;
        }

        $wishlist = Wishlist::where('user_id', Auth::id())->findOrFail($id);
        $wishlist->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'status' => 'removed',
                'message' => 'Product removed from wishlist!',
            ]);
        }

        return back()->with('success', 'Product removed from wishlist!');
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        if (!Auth::user()->isCustomer()) {
            return response()->json([
                'status' => 'forbidden',
                'message' => 'Wishlist is available for customer accounts only.',
            ], 403);
        }

        $wishlist = Wishlist::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            return response()->json(['status' => 'removed', 'message' => 'Removed from wishlist']);
        } else {
            Wishlist::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
            ]);
            return response()->json(['status' => 'added', 'message' => 'Added to wishlist']);
        }
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

    public function apiDestroyApi($id)
    {
        request()->headers->set('Accept', 'application/json');
        return $this->destroy($id);
    }
}
