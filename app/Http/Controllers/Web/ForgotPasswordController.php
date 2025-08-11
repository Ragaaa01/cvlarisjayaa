<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:web');
    }

    // Menampilkan form lupa password
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    // Memproses input email dan redirect ke form reset
    public function verifyEmail(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'email', 'exists:akuns,email'],
            ]);

            // Generate token sementara
            $token = Str::random(60);
            // Simpan token dan email di session dengan waktu kadaluarsa (10 menit)
            session(['reset_password_token' => $token, 'reset_email' => $request->email, 'token_expires_at' => now()->addMinutes(10)]);

            // Redirect ke form reset password
            return redirect()->route('password.reset', ['token' => $token, 'email' => $request->email])
                             ->with('status', 'Email valid. Silakan masukkan password baru.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memverifikasi email: ' . $e->getMessage())->withInput();
        }
    }
}