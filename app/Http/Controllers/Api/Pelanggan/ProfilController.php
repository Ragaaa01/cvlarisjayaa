<?php

namespace App\Http\Controllers\Api\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Orang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Exception;

class ProfilController extends Controller
{
    /**
     * Menampilkan data profil lengkap milik pengguna yang sedang login.
     */
    public function show(Request $request)
    {
        try {
            $akun = $request->user()->load(['orang', 'deposit']);

            return response()->json([
                'success' => true,
                'message' => 'Data profil berhasil diambil.',
                'data' => $akun
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data profil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Memperbarui data diri pengguna (orangs).
     */
    public function update(Request $request)
    {
        $akun = $request->user();
        $orang = $akun->orang;

        if (!$orang) {
            return response()->json(['success' => false, 'message' => 'Data profil tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            // Tambahkan validasi lain jika diperlukan
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $validator->errors()], 422);
        }

        try {
            $orang->update($request->only('nama_lengkap', 'alamat'));

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui.',
                'data' => $orang
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui profil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengubah password login pengguna.
     */
    public function ubahPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password_lama' => 'required|string',
            'password_baru' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $validator->errors()], 422);
        }

        try {
            $akun = $request->user();

            // Cek apakah password lama sesuai
            if (!Hash::check($request->password_lama, $akun->password)) {
                return response()->json(['success' => false, 'message' => 'Password lama tidak sesuai.'], 401);
            }

            // Update password baru
            $akun->update([
                'password' => Hash::make($request->password_baru)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diubah.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah password: ' . $e->getMessage()
            ], 500);
        }
    }
}
