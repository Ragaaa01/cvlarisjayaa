<?php

namespace App\Http\Controllers\Api;

use \App\Mail\PasswordResetMail;
use App\Http\Controllers\Controller;
use App\Models\Akun;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), ['email' => 'required|email|exists:akuns,email']);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Email tidak terdaftar.'], 404);
        }

        // Generate token
        $token = Str::random(60);

        // Simpan token ke database
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => now()]
        );

        // Kirim email notifikasi (tanpa link, hanya token)
        // Anda perlu membuat Mailable: php artisan make:mail PasswordResetMail
        try {
            Mail::to($request->email)->send(new PasswordResetMail($token));
            return response()->json(['success' => true, 'message' => 'Token reset password telah dikirim ke email Anda.'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengirim email: ' . $e->getMessage()], 500);
        }
    }

    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email|exists:akuns,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'data' => ['errors' => $validator->errors()]], 422);
        }

        // Verifikasi token
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$resetRecord || Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            return response()->json(['success' => false, 'message' => 'Token tidak valid atau telah kedaluwarsa.'], 400);
        }
        
        // Update password di tabel akuns
        $akun = Akun::where('email', $request->email)->first();
        $akun->password = Hash::make($request->password);
        $akun->save();

        // Hapus token setelah digunakan
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['success' => true, 'message' => 'Password Anda berhasil diubah.'], 200);
    }
}
