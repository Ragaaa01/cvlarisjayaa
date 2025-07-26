<?php

namespace App\Http\Controllers\Api\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Tabung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class TabungController extends Controller
{
    /**
     * Menampilkan daftar tabung dengan filter dan relasi.
     */
    public function index(Request $request)
    {
        try {
            $query = Tabung::with(['jenisTabung', 'statusTabung']);

            // Contoh filter berdasarkan status
            if ($request->has('id_status_tabung')) {
                $query->where('id_status_tabung', $request->id_status_tabung);
            }

            // Contoh pencarian berdasarkan kode
            if ($request->has('search')) {
                $query->where('kode_tabung', 'like', '%' . $request->search . '%');
            }

            $tabungs = $query->paginate(15);

            return response()->json([
                'success' => true,
                'message' => 'Data tabung berhasil diambil.',
                'data' => $tabungs
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan tabung baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_tabung' => 'required|string|max:255|unique:tabungs',
            'id_jenis_tabung' => 'required|exists:jenis_tabungs,id_jenis_tabung',
            'id_status_tabung' => 'required|exists:status_tabungs,id_status_tabung',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $tabung = Tabung::create($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Tabung berhasil ditambahkan.',
                'data' => $tabung
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan satu tabung spesifik.
     */
    public function show($id)
    {
        try {
            $tabung = Tabung::with(['jenisTabung', 'statusTabung'])->findOrFail($id);
            return response()->json([
                'success' => true,
                'message' => 'Detail tabung berhasil diambil.',
                'data' => $tabung
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ], 404);
        }
    }

    /**
     * BARU: Menampilkan satu tabung spesifik berdasarkan KODE TABUNG.
     * Endpoint ini sangat cocok untuk fitur scan QR code.
     */
    public function showByKode($kode_tabung)
    {
        try {
            $tabung = Tabung::with(['jenisTabung', 'statusTabung'])
                ->where('kode_tabung', $kode_tabung)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Detail tabung berdasarkan kode berhasil diambil.',
                'data' => $tabung
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tabung dengan kode tersebut tidak ditemukan.'
            ], 404);
        }
    }

    /**
     * Memperbarui tabung yang ada.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'kode_tabung' => 'required|string|max:255|unique:tabungs,kode_tabung,' . $id . ',id_tabung',
            'id_jenis_tabung' => 'required|exists:jenis_tabungs,id_jenis_tabung',
            'id_status_tabung' => 'required|exists:status_tabungs,id_status_tabung',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $tabung = Tabung::findOrFail($id);
            $tabung->update($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Tabung berhasil diperbarui.',
                'data' => $tabung
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data atau data tidak ditemukan.'
            ], 500);
        }
    }

    /**
     * Menghapus tabung.
     */
    public function destroy($id)
    {
        try {
            $tabung = Tabung::findOrFail($id);
            // Tambahkan logika untuk mencegah penghapusan jika tabung sedang dipinjam
            if ($tabung->id_status_tabung == 2) { // Asumsi 2 adalah 'dipinjam'
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus: Tabung sedang dalam status dipinjam.'
                ], 400);
            }
            $tabung->delete();
            return response()->json([
                'success' => true,
                'message' => 'Tabung berhasil dihapus.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data atau data tidak ditemukan.'
            ], 500);
        }
    }
}
