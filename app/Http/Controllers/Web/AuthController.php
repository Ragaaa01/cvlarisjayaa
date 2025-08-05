<?php

namespace App\Http\Controllers\Web;

use App\Models\Akun;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Exception;

class AuthController extends Controller
{
   public function __construct()
    {
        // Terapkan middleware 'guest' untuk showLoginForm agar hanya bisa diakses oleh pengguna yang belum login
        $this->middleware('guest:web')->only('showLoginForm');
        // Terapkan middleware 'auth' untuk melindungi metode lain kecuali login dan showLoginForm
        $this->middleware('auth:web')->except(['showLoginForm', 'login']);
        // Terapkan middleware 'role' untuk memverifikasi role, kecuali login dan logout
        $this->middleware('role:administrator,karyawan')->except(['showLoginForm', 'login', 'logout']);
    }

    public function showLoginForm()
    {
        try {
            // Jika pengguna sudah login, arahkan ke halaman sesuai role
            if (Auth::guard('web')->check()) {
                $user = Auth::guard('web')->user();
                if ($user->role) {
                    if (in_array($user->role->nama_role, ['administrator', 'karyawan'])) {
                        return redirect()->route('dashboard');
                    } elseif ($user->role->nama_role === 'pelanggan') {
                        return redirect()->route('user.home');
                    }
                }
                // Jika role tidak valid, logout dan kembali ke login
                Auth::guard('web')->logout();
                return redirect()->route('login')->with('error', 'Role tidak valid.');
            }
            return view('login'); // Tampilkan halaman login jika belum login
        } catch (Exception $e) {
            return redirect()->route('login')->with('error', 'Gagal memuat halaman login: ' . $e->getMessage());
        }
    }

    public function login(Request $request)
    {
        try {
            // Validasi input
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);

            // Coba autentikasi
            if (Auth::guard('web')->attempt($credentials)) {
                $request->session()->regenerate();
                $user = Auth::guard('web')->user();

                // Check if akun status aktif
                if (!$user->status_aktif) {
                    Auth::guard('web')->logout();
                    return back()->with('error', 'Akun Anda belum aktif.');
                }

                // Redirect berdasarkan nama_role
                if ($user->role && in_array($user->role->nama_role, ['administrator', 'karyawan'])) {
                    return redirect()->intended(route('dashboard')); // Administrator dan karyawan ke dashboard
                } elseif ($user->role && $user->role->nama_role === 'pelanggan') {
                    return redirect()->intended(route('user.home')); // Pelanggan ke user.home
                } else {
                    Auth::guard('web')->logout();
                    return back()->with('error', 'Role tidak valid.');
                }
            }

            return back()->with('error', 'Email atau password salah.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Tangani error validasi
            return back()->withErrors($e->errors())->withInput()->with('error', 'Validasi gagal, periksa input Anda.');
        } catch (Exception $e) {
            // Tangani error umum lainnya
            return back()->with('error', 'Terjadi kesalahan saat login: ' . $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        try {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('success', 'Logout berhasil.');
        } catch (Exception $e) {
            return redirect()->route('login')->with('error', 'Gagal logout: ' . $e->getMessage());
        }
    }
}