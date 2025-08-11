<?php

namespace App\Http\Controllers\Api\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Mitra;
use App\Models\OrangMitra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;

class MitraPelangganController extends Controller
{
    /**
     * Mencari mitra yang belum memiliki perwakilan aktif.
     * Digunakan untuk fitur autocomplete di frontend.
     */
    public function search(Request $request)
    {
        try {
            $namaMitra = $request->query('nama');

            if (!$namaMitra || strlen($namaMitra) < 3) {
                return response()->json([
                    'success' => true,
                    'message' => 'Query terlalu pendek.',
                    'data'    => []
                ], 200);
            }

            // Jika query valid, lanjutkan pencarian
            $mitras = Mitra::where('nama_mitra', 'like', '%' . $namaMitra . '%')
                ->where('verified', true) // Hanya mitra yang sudah diverifikasi
                ->whereDoesntHave('orangMitras', function ($query) {
                    $query->where('status_valid', true); // Yang belum punya perwakilan valid
                })
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data mitra berhasil diambil.',
                'data'    => $mitras
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mencari mitra: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mendaftarkan pengguna yang sedang login sebagai perwakilan dari mitra yang dipilih.
     */
    public function daftar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_mitra' => 'required|exists:mitras,id_mitra',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'data' => ['errors' => $validator->errors()]], 422);
        }

        DB::beginTransaction();
        try {
            $akun = $request->user();
            $idMitra = $request->id_mitra;

            // Double check: Pastikan mitra ini masih tersedia
            $isTaken = OrangMitra::where('id_mitra', $idMitra)->where('status_valid', true)->exists();
            if ($isTaken) {
                throw new Exception("Mitra ini sudah memiliki perwakilan.");
            }

            // Cek apakah pengguna ini sudah menjadi perwakilan mitra lain
            $isAlreadyRep = OrangMitra::where('id_orang', $akun->id_orang)->where('status_valid', true)->exists();
            if ($isAlreadyRep) {
                throw new Exception("Anda sudah terdaftar sebagai perwakilan mitra lain.");
            }

            $pendaftaran = OrangMitra::create([
                'id_orang' => $akun->id_orang,
                'id_mitra' => $idMitra,
                'status_valid' => true, // Langsung valid
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Anda berhasil terdaftar sebagai perwakilan mitra.',
                'data'    => $pendaftaran
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal mendaftar: ' . $e->getMessage()], 500);
        }
    }
}
