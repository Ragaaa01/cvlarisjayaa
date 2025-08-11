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
        $this->middleware('guest:web')->only('showLoginForm');
        $this->middleware('auth:web')->except(['showLoginForm', 'login', 'logout']);
        $this->middleware('role:administrator,karyawan,pelanggan')->except(['showLoginForm', 'login', 'logout']);
    }

    public function showLoginForm()
    {
        try {
            \Log::info('AuthController: Accessing showLoginForm', [
                'authenticated' => Auth::guard('web')->check(),
                'session_id' => request()->session()->getId()
            ]);
            return view('login');
        } catch (Exception $e) {
            \Log::error('AuthController: Error in showLoginForm', ['error' => $e->getMessage()]);
            return view('login')->with('error', 'Gagal memuat halaman login: ' . $e->getMessage());
        }
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);

            if (Auth::guard('web')->attempt($credentials)) {
                $request->session()->regenerate();
                $user = Auth::guard('web')->user();

                \Log::info('AuthController: Login attempt successful', [
                    'email' => $user->email,
                    'role' => $user->role ? $user->role->nama_role : 'null'
                ]);

                if (!$user->status_aktif) {
                    Auth::guard('web')->logout();
                    $request->session()->invalidate();
                    \Log::warning('AuthController: Login failed - Inactive account', ['email' => $user->email]);
                    return back()->with('error', 'Akun Anda belum aktif.');
                }

                if (!$user->role || !in_array($user->role->nama_role, ['administrator', 'karyawan', 'pelanggan'])) {
                    Auth::guard('web')->logout();
                    $request->session()->invalidate();
                    \Log::warning('AuthController: Login failed - Invalid role', ['email' => $user->email]);
                    return back()->with('error', 'Akun Anda tidak memiliki role yang valid.');
                }

                if ($user->role->nama_role === 'administrator') {
                    \Log::info('AuthController: Redirecting to admin dashboard', ['email' => $user->email]);
                    return redirect()->intended(route('dashboard'));
                } elseif ($user->role->nama_role === 'karyawan') {
                    \Log::info('AuthController: Redirecting to karyawan dashboard', ['email' => $user->email]);
                    return redirect()->intended(route('karyawan.dashboard'));
                } elseif ($user->role->nama_role === 'pelanggan') {
                    \Log::info('AuthController: Redirecting to user.home for pelanggan', ['email' => $user->email]);
                    return redirect()->intended(route('user.home'));
                }
            }

            \Log::warning('AuthController: Login failed - Invalid credentials', ['email' => $request->email]);
            return back()->with('error', 'Email atau password salah.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('AuthController: Validation error in login', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput()->with('error', 'Validasi gagal, periksa input Anda.');
        } catch (Exception $e) {
            \Log::error('AuthController: Error in login', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat login: ' . $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        try {
            \Log::info('AuthController: User logging out', ['email' => Auth::guard('web')->check() ? Auth::guard('web')->user()->email : 'N/A']);
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('success', 'Logout berhasil.');
        } catch (Exception $e) {
            \Log::error('AuthController: Error in logout', ['error' => $e->getMessage()]);
            return redirect()->route('login')->with('error', 'Gagal logout: ' . $e->getMessage());
        }
    }
}