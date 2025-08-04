<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\Orang;
use App\Models\Perorangan;
use App\Models\Tagihan;
use Exception;
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
    // public function register(Request $request)
    // {
    //     $messages = [
    //         'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
    //         'nik.required' => 'NIK wajib diisi.',
    //         'nik.size' => 'NIK harus terdiri dari 16 digit.',
    //         'nik.unique' => 'NIK sudah terdaftar.',
    //         'no_telepon.required' => 'Nomor telepon wajib diisi.',
    //         'no_telepon.unique' => 'Nomor telepon sudah terdaftar.',
    //         'id_kelurahan.required' => 'Kelurahan wajib dipilih.',
    //         'alamat.required' => 'Alamat wajib diisi.',
    //         'email.required' => 'Email wajib diisi.',
    //         'email.email' => 'Format email tidak valid.',
    //         'email.unique' => 'Email sudah terdaftar.',
    //         'password.required' => 'Password wajib diisi.',
    //         'password.min' => 'Password minimal harus 6 karakter.',
    //         'password.confirmed' => 'Konfirmasi password tidak cocok.',
    //     ];

    //     $validator = Validator::make($request->all(), [
    //         'nama_lengkap' => 'required|string|max:255',
    //         'nik' => 'required|string|size:16|unique:orangs,nik',
    //         'no_telepon' => 'required|string|unique:orangs,no_telepon|max:15',
    //         'id_kelurahan' => 'required|exists:kelurahans,id_kelurahan',
    //         'alamat' => 'required|string',
    //         'email' => 'required|string|email|max:255|unique:akuns,email',
    //         'password' => 'required|string|min:6|confirmed',
    //     ], $messages);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validasi gagal.',
    //             'data'    => ['errors' => $validator->errors()]
    //         ], 422);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $orang = Orang::create([
    //             'nama_lengkap' => $request->nama_lengkap,
    //             'nik' => $request->nik,
    //             'no_telepon' => $request->no_telepon,
    //             'id_kelurahan' => $request->id_kelurahan,
    //             'alamat' => $request->alamat,
    //         ]);

    //         // Role 'pelanggan' diasumsikan memiliki id_role = 2
    //         $akun = Akun::create([
    //             'id_role' => 2,
    //             'id_orang' => $orang->id_orang,
    //             'email' => $request->email,
    //             'password' => Hash::make($request->password),
    //             'status_aktif' => true,
    //         ]);

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Registrasi berhasil. Akun Anda sudah aktif dan bisa digunakan.',
    //             'data'    => $akun->load('orang')
    //         ], 201);
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Registrasi gagal: ' . $e->getMessage(),
    //             'data'    => null
    //         ], 500);
    //     }
    // }

    /**
     * Mendaftarkan pelanggan baru atau mengaktifkan akun non-aplikasi yang sudah ada.
     */
    public function register(Request $request)
    {
        $messages = [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.size' => 'NIK harus terdiri dari 16 digit.',
            'no_telepon.required' => 'Nomor telepon wajib diisi.',
            'id_kelurahan.required' => 'Kelurahan wajib dipilih.',
            'alamat.required' => 'Alamat wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];

        // Validasi awal (tanpa unique NIK dan no_telepon)
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'nik' => 'required|string|size:16',
            'no_telepon' => 'required|string|max:15',
            'id_kelurahan' => 'required|exists:kelurahans,id_kelurahan',
            'alamat' => 'required|string',
            'email' => 'required|string|email|max:255|unique:akuns,email',
            'password' => 'required|string|min:6|confirmed',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'data'    => ['errors' => $validator->errors()]
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Cek apakah ada orang yang sudah terdaftar dengan NIK atau No. Telepon ini
            $orang = Orang::where('nik', $request->nik)
                ->orWhere('no_telepon', $request->no_telepon)
                ->first();

            if ($orang) {
                // --- LOGIKA JIKA ORANG SUDAH ADA ---
                $akun = $orang->akun;

                // Jika akun tidak ada (kasus aneh), anggap sebagai error
                if (!$akun) {
                    throw new Exception("Data orang ditemukan tetapi akun terkait tidak ada.");
                }

                // Cek apakah emailnya masih dummy
                if (str_ends_with($akun->email, '@nonaplikasi.com')) {
                    // Update akun yang sudah ada dengan data baru
                    $akun->update([
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                    ]);
                    $message = 'Akun Anda yang sudah ada berhasil diaktifkan untuk akses aplikasi.';
                } else {
                    // Akun sudah aktif dengan email asli, tolak pendaftaran
                    return response()->json([
                        'success' => false,
                        'message' => 'NIK atau Nomor Telepon sudah terdaftar dengan akun lain.',
                        'data'    => null
                    ], 409); // 409 Conflict
                }
            } else {
                // --- LOGIKA JIKA ORANG BELUM ADA (PENDAFTARAN BARU) ---
                $orangBaru = Orang::create([
                    'nama_lengkap' => $request->nama_lengkap,
                    'nik' => $request->nik,
                    'no_telepon' => $request->no_telepon,
                    'id_kelurahan' => $request->id_kelurahan,
                    'alamat' => $request->alamat,
                ]);

                // Role 'pelanggan' diasumsikan memiliki id_role = 2
                $akun = Akun::create([
                    'id_role' => 2,
                    'id_orang' => $orangBaru->id_orang,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'status_aktif' => true,
                ]);
                $message = 'Registrasi berhasil. Akun Anda sudah aktif dan bisa digunakan.';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => $akun->load('orang')
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Registrasi gagal: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'data'    => ['errors' => $validator->errors()]
            ], 422);
        }

        if (!Auth::guard('web')->attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
                'data'    => null
            ], 401);
        }

        $akun = Akun::where('email', $request->email)->firstOrFail();

        if (!$akun->status_aktif) {
            Auth::guard('web')->logout();
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak aktif. Silakan hubungi administrator.',
                'data'    => null
            ], 403);
        }

        $token = $akun->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data'    => [
                'token' => $token,
                'token_type' => 'Bearer',
                'akun' => $akun->load('orang', 'role')
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        // [PERBAIKAN FINAL] Menghapus SEMUA token milik pengguna.
        // Ini adalah cara yang paling andal dan aman untuk memastikan logout.
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
            'data'    => null
        ], 200);
    }
}
