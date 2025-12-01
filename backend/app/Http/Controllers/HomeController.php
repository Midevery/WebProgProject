<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        // If user is not logged in, redirect to sign in
        if (!Auth::check()) {
            return redirect()->route('signin');
        }
        
        $user = Auth::user();
        
        // Redirect based on role
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isArtist()) {
            return redirect()->route('artist.dashboard');
        }
        
        // Redirect customers to dashboard
        return redirect()->route('customer.dashboard');
    }
}
