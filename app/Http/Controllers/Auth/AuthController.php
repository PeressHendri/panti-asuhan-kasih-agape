<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Check if user is active
            if (!$user->is_active) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.',
                ]);
            }

            // Redirect based on role
            return $this->redirectUser($user);
        }

        throw ValidationException::withMessages([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ]);
    }

    public function logout(Request $request)
    {
        $email = Auth::user()->email ?? null;
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        if ($email) {
            $request->session()->flash('last_email', $email);
        }
        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }

    private function redirectUser($user)
    {
        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard')->with('success', 'Selamat datang, Admin!');
            case 'pengasuh':
                return redirect()->route('pengasuh.dashboard')->with('success', 'Selamat datang, Pengasuh!');
            case 'donatur':
                return redirect()->route('donatur.dashboard')->with('success', 'Selamat datang, Donatur!');
            default:
                return redirect()->route('home')->with('success', 'Selamat datang!');
        }
    }
}