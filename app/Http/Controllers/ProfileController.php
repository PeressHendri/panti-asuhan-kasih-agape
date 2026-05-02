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

        // Simpan pengaturan AI & mode absensi (hanya admin, jika form dari halaman admin)
        if ($user->role === 'admin' && $request->input('is_admin_form') === '1') {
            // Checkbox OFF = tidak terkirim di HTTP, jadi cek via has()
            $manualEnabled = $request->has('enable_manual_attendance');
            \Illuminate\Support\Facades\Cache::forever('enable_manual_attendance', $manualEnabled);

            $threshold = $request->input('confidence_threshold', 75);
            \Illuminate\Support\Facades\Cache::forever('confidence_threshold', (int) $threshold);
        }

        // Log aktivitas (opsional)
        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'activity' => 'Update profil',
            'status' => 'Berhasil'
        ]);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
} 