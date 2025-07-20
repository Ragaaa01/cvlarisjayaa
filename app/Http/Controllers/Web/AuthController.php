<?php

namespace App\Http\Controllers\Web;

use App\Models\Akun;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
     public function showLoginForm()
    {
        return view('login'); // Sesuaikan dengan lokasi view Anda
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::guard('web')->user();

            // Check if akun status aktif
            if (!$user->status_aktif) {
                Auth::guard('web')->logout();
                return back()->with('error', 'Akun Anda belum aktif.');
            }

            // Redirect berdasarkan nama_role
            if ($user->role && $user->role->nama_role === 'administrator') {
                return redirect()->intended(route('dashboard')); // Sesuaikan dengan route admin.dashboard
            } elseif ($user->role && in_array($user->role->nama_role, ['karyawan', 'pelanggan'])) {
                return redirect()->intended(route('user.home')); // Sesuaikan dengan route user.home
            } else {
                Auth::guard('web')->logout();
                return back()->with('error', 'Role tidak valid.');
            }
        }

        return back()->with('error', 'Email atau password salah.');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Logout berhasil.');
    }
}
