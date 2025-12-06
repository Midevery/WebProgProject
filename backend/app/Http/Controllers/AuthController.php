<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showSignIn()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isSeller()) {
                return redirect()->route('seller.dashboard');
            } else {
                return redirect()->route('home');
            }
        }
        if (request()->expectsJson()) {
            return response()->json(['message' => 'Please sign in']);
        }
        return view('auth.signin');
    }

    public function showSignUp()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isSeller()) {
                return redirect()->route('seller.dashboard');
            } else {
                return redirect()->route('home');
            }
        }
        if (request()->expectsJson()) {
            return response()->json(['message' => 'Please sign up']);
        }
        return view('auth.signup');
    }

    public function signIn(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        $credentials = [
            $loginField => $request->login,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            if ($user->isSeller()) {
                return redirect()->route('seller.dashboard');
            } else {
                return redirect()->route('home');
            }
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ])->onlyInput('login');
    }

    public function signUp(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:Male,Female',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $userData = [
            'username' => $request->username,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'role' => 'customer',
        ];

        if ($request->hasFile('profile_image')) {
            $uploadPath = public_path('images/profiles');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $image = $request->file('profile_image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->move($uploadPath, $imageName);
            
            $userData['profile_image'] = 'images/profiles/' . $imageName;
        }

        $user = User::create($userData);

        Auth::login($user);

        if ($user->isSeller()) {
            return redirect()->route('seller.dashboard');
        }
        
        return redirect()->route('customer.dashboard');
    }

    public function signOut(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('signin');
    }

    public function apiSignIn(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginField => $request->login,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            return response()->json([
                'message' => 'Signed in successfully.',
                'user' => $user,
            ]);
        }

        return response()->json([
            'message' => 'The provided credentials do not match our records.',
        ], 422);
    }

    public function apiSignUp(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|in:customer,seller',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:Male,Female',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $userData = [
            'username' => $request->username,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
        ];

        if ($request->hasFile('profile_image')) {
            $uploadPath = public_path('images/profiles');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $image = $request->file('profile_image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->move($uploadPath, $imageName);

            $userData['profile_image'] = 'images/profiles/' . $imageName;
        }

        $user = User::create($userData);
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Signed up successfully.',
            'user' => $user,
            'redirect' => $user->isSeller() ? route('seller.dashboard') : route('customer.dashboard'),
        ], 201);
    }

    public function apiSignOut(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Signed out successfully.',
        ]);
    }
}
