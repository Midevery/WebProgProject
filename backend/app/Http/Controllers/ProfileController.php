<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if ($request->has('username')) {
            $request->request->remove('username');
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'date_of_birth' => 'nullable|date|before:today',
                'gender' => 'nullable|in:Male,Female',
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                $errors = $e->errors();
                unset($errors['username']);
                if (empty($errors)) {
                    $data = $request->only(['name', 'email', 'phone', 'address', 'date_of_birth', 'gender']);
                    if ($request->hasFile('profile_image')) {
                        $uploadPath = public_path('images/profiles');
                        if (!file_exists($uploadPath)) {
                            mkdir($uploadPath, 0755, true);
                        }
                        $image = $request->file('profile_image');
                        $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                        $image->move($uploadPath, $imageName);
                        if ($user->profile_image && file_exists(public_path($user->profile_image))) {
                            @unlink(public_path($user->profile_image));
                        }
                        $data['profile_image'] = 'images/profiles/' . $imageName;
                    }
                    $user->update($data);
                    return response()->json([
                        'message' => 'Profile updated successfully!',
                        'user' => $user->refresh(),
                    ]);
                }
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $errors,
                ], 422);
            }
            throw $e;
        }

        $data = $request->only(['name', 'email', 'phone', 'address', 'date_of_birth', 'gender']);

        if ($request->hasFile('profile_image')) {
            $uploadPath = public_path('images/profiles');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $image = $request->file('profile_image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->move($uploadPath, $imageName);
            
            if ($user->profile_image && file_exists(public_path($user->profile_image))) {
                @unlink(public_path($user->profile_image));
            }
            
            $data['profile_image'] = 'images/profiles/' . $imageName;
        }

        $user->update($data);

        $user->refresh();

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'message' => 'Profile updated successfully!',
                'user' => $user,
            ]);
        }

        return back()->with('success', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'current_password' => 'required',
                'password' => 'required|confirmed|min:8',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Current password is incorrect',
                    'errors' => ['current_password' => ['Current password is incorrect']],
                ], 422);
            }

            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'message' => 'Password updated successfully!',
            ]);
        }

        return back()->with('success', 'Password updated successfully!');
    }

    public function apiMe(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    public function apiUpdate(Request $request)
    {
        $user = Auth::user();

        $request->headers->set('Accept', 'application/json');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:Male,Female',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['name', 'email', 'phone', 'address', 'date_of_birth', 'gender']);

        if ($request->hasFile('profile_image')) {
            $uploadPath = public_path('images/profiles');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $image = $request->file('profile_image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->move($uploadPath, $imageName);

            if ($user->profile_image && file_exists(public_path($user->profile_image))) {
                @unlink(public_path($user->profile_image));
            }

            $data['profile_image'] = 'images/profiles/' . $imageName;
        }

        $user->update($data);

        $user->refresh();

        return response()->json([
            'message' => 'Profile updated successfully!',
            'user' => $user,
        ]);
    }

    public function apiUpdatePassword(Request $request)
    {
        $request->headers->set('Accept', 'application/json');
        return $this->updatePassword($request);
    }
}
