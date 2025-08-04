<?php

namespace App\Http\Controllers\Api\Administrator;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfilController extends Controller
{
    /**
     * Mengambil data profil lengkap dari administrator yang sedang login.
     */
    public function show(Request $request)
    {
        try {
            $akun = $request->user()->load(['orang.kelurahan.kecamatan.kabupaten.provinsi', 'role']);
            return response()->json(['success' => true, 'message' => 'Data profil berhasil diambil.', 'data' => $akun], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data profil.'], 500);
        }
    }

    /**
     * Memperbarui data profil (orang) dari administrator yang sedang login.
     */
    public function update(Request $request)
    {
        $orang = $request->user()->orang;

        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'no_telepon' => 'required|string|max:15|unique:orangs,no_telepon,' . $orang->id_orang . ',id_orang',
            'id_kelurahan' => 'nullable|exists:kelurahans,id_kelurahan',
            'alamat' => 'nullable|string',
            'nik' => 'nullable|string|size:16|unique:orangs,nik,' . $orang->id_orang . ',id_orang',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'data' => ['errors' => $validator->errors()]], 422);
        }

        try {
            $orang->update($request->all());
            return response()->json(['success' => true, 'message' => 'Profil berhasil diperbarui.', 'data' => $orang], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui profil.'], 500);
        }
    }

    /**
     * Mengubah password dari administrator yang sedang login.
     */
    public function ubahPassword(Request $request)
    {
        $akun = $request->user();

        $validator = Validator::make($request->all(), [
            'password_lama' => 'required|string',
            'password_baru' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'data' => ['errors' => $validator->errors()]], 422);
        }

        if (!Hash::check($request->password_lama, $akun->password)) {
            return response()->json(['success' => false, 'message' => 'Password lama tidak sesuai.'], 401);
        }

        try {
            $akun->update(['password' => Hash::make($request->password_baru)]);
            return response()->json(['success' => true, 'message' => 'Password berhasil diubah.'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengubah password.'], 500);
        }
    }
}
