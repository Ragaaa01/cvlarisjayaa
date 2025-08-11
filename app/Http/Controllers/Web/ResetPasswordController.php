<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:web');
    }

    // Menampilkan form reset password
    public function showResetForm(Request $request, $token)
    {
        $email = session('reset_email');
        $sessionToken = session('reset_password_token');
        $expiresAt = session('token_expires_at');

        // Validasi token dan email
        if (!$email || !$sessionToken || $token !== $sessionToken || now()->greaterThan($expiresAt)) {
            return redirect()->route('password.request')->with('error', 'Token tidak valid atau telah kadaluarsa.');
        }

        return view('auth.passwords.reset', compact('token', 'email'));
    }

    // Proses reset password
    public function reset(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required',
                'email' => ['required', 'email', 'exists:akuns,email'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $email = session('reset_email');
            $sessionToken = session('reset_password_token');
            $expiresAt = session('token_expires_at');

            // Validasi token
            if ($request->token !== $sessionToken || $request->email !== $email || now()->greaterThan($expiresAt)) {
                return redirect()->route('password.request')->with('error', 'Token tidak valid atau telah kadaluarsa.');
            }

            // Update password
            $akun = Akun::where('email', $request->email)->first();
            $akun->update([
                'password' => Hash::make($request->password),
            ]);

            // Hapus session setelah sukses
            session()->forget(['reset_password_token', 'reset_email', 'token_expires_at']);

            return redirect()->route('login')->with('success', 'Password telah berhasil direset. Silakan login.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mereset password: ' . $e->getMessage())->withInput();
        }
    }
}