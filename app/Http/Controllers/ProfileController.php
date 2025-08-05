<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|confirmed|min:6',
            'photo' => 'nullable|image|max:10000',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        if ($request->hasFile('photo')) {
            $user->photo = $request->file('photo')->store('profile', 'public');
        }
        $user->save();

        // Log aktivitas (opsional)
        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'activity' => 'Update profil',
            'status' => 'Berhasil'
        ]);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
} 