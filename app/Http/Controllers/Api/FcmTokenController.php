<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class FcmTokenController extends Controller
{
    /**
     * Menyimpan atau memperbarui FCM token untuk pengguna yang sedang login.
     * Endpoint ini harus dipanggil oleh aplikasi frontend setiap kali
     * pengguna berhasil login atau saat token di-refresh oleh Firebase.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'nama_perangkat' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $akun = $request->user(); // Mengambil data pengguna yang sedang login

            // Menggunakan updateOrCreate untuk efisiensi:
            // 1. Mencari token yang sama.
            // 2. Jika ditemukan, perbarui id_akun (jika pengguna login dengan akun lain di perangkat yang sama).
            // 3. Jika tidak ditemukan, buat record baru.
            $fcmToken = FcmToken::updateOrCreate(
                [
                    'token' => $request->token,
                ],
                [
                    'id_akun' => $akun->id_akun,
                    'nama_perangkat' => $request->nama_perangkat,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'FCM token berhasil disimpan.',
                'data' => $fcmToken
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan FCM token: ' . $e->getMessage()
            ], 500);
        }
    }
}
