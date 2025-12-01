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

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:Male,Female',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['name', 'email', 'phone', 'address', 'date_of_birth', 'gender']);

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Create directory if it doesn't exist
            $uploadPath = public_path('images/profiles');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $image = $request->file('profile_image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->move($uploadPath, $imageName);
            
            // Delete old image if exists
            if ($user->profile_image && file_exists(public_path($user->profile_image))) {
                @unlink(public_path($user->profile_image));
            }
            
            $data['profile_image'] = 'images/profiles/' . $imageName;
        }

        $user->update($data);

        return back()->with('success', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password updated successfully!');
    }
}
