<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\Perorangan;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /**
     * Mendaftarkan pelanggan baru, membuat data perorangan, akun, dan master tagihan.
     */
    public function register(Request $request)
    {
        // 1. Validasi input dari Flutter
        $validator = Validator::make($request->all(), [
            'email'        => 'required|string|email|max:255|unique:akuns,email',
            'password'     => ['required', 'confirmed', Password::min(8)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'data'    => ['errors' => $validator->errors()]
            ], 422); // 422 Unprocessable Entity
        }

        // 2. Gunakan DB Transaction untuk memastikan semua data konsisten
        try {
            $akun = Akun::create([
                // id_perorangan dibiarkan null, akan diisi oleh admin
                'id_perorangan' => null,
                // id_role untuk pelanggan (asumsi ID = 2)
                'id_role'       => 2,
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
                // Akun baru wajib tidak aktif sampai dikonfirmasi admin
                'status_aktif'  => false,
            ]);
        } catch (\Exception $e) {
            // Jika terjadi error, kembalikan respons gagal
            return response()->json([
                'success' => false,
                'message' => 'Registrasi gagal, terjadi kesalahan pada server.',
                'data'    => null
            ], 500);
        }

        // 3. Kirim respons sukses
        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil. Akun Anda akan segera diaktifkan oleh Administrator.',
            'data'    => [
                'email' => $akun->email,
                'role' => $akun->role,
                'status_aktif' => $akun->status_aktif,
            ]
        ], 201); // 201 Created
    }

    /**
     * Melakukan login pengguna dan mengembalikan token otentikasi.
     */
    public function login(Request $request)
    {
        // 1. Validasi input
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'data'    => ['errors' => $validator->errors()]
            ], 422);
        }

        // 2. Coba autentikasi pengguna
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
                'data'    => null
            ], 401); // 401 Unauthorized
        }

        // 3. Dapatkan data akun yang berhasil login
        $akun = Akun::where('email', $request->email)->firstOrFail();

        // Pengecekan tambahan (misalnya, akun aktif)
        if (!$akun->status_aktif) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda belum aktif. Silakan hubungi Administrator.',
                'data'    => null
            ], 403); // 403 Forbidden
        }

        // 4. Buat token Sanctum
        $token = $akun->createToken('auth_token')->plainTextToken;

        // 5. Kirim respons sukses beserta token dan data pengguna
        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data'    => [
                'token' => $token,
                'akuns'  => [
                    'id_akun' => $akun->id_akun,
                    'email'   => $akun->email,
                    'role'    => $akun->role->nama_role, // Asumsi ada relasi 'role' di model Akun
                ]
            ]
        ], 200);
    }

    /**
     * Melakukan logout pengguna dengan menghapus token saat ini.
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        // Ambil token yang sedang digunakan untuk request ini
        $token = $user->currentAccessToken();

        // --- PERBAIKAN: Lakukan pengecekan untuk memastikan token valid ---
        // Ini akan mencegah error jika token karena suatu alasan tidak ditemukan
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        } else {
            // Jika token tidak valid, kita bisa mencatatnya untuk debugging,
            // tapi tetap lanjutkan proses logout agar klien bisa membersihkan sesinya.
            Log::warning('Gagal menghapus token saat logout: Token tidak valid atau tidak ditemukan.', [
                'user_id' => $user->id_akun
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
            'data'    => null
        ], 200);
    }
}
