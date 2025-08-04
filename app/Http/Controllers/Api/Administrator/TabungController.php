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
            $query = Tabung::with(['jenisTabung', 'statusTabung', 'kepemilikan']);

            // Filter berdasarkan status
            if ($request->has('id_status_tabung')) {
                $query->where('id_status_tabung', $request->id_status_tabung);
            }

            // Pencarian berdasarkan kode tabung
            if ($request->has('search')) {
                $query->where('kode_tabung', 'like', '%' . $request->search . '%');
            }

            $tabungs = $query->latest()->paginate(20);

            return response()->json([
                'success' => true,
                'message' => 'Data tabung berhasil diambil.',
                'data'    => $tabungs
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    /**
     * Menyimpan tabung baru.
     */
    public function store(Request $request)
    {
        $messages = [
            'kode_tabung.required' => 'Kode tabung wajib diisi.',
            'kode_tabung.unique'   => 'Kode tabung ini sudah terdaftar.',
            'id_jenis_tabung.required' => 'Jenis tabung wajib dipilih.',
            'id_jenis_tabung.exists'   => 'Jenis tabung yang dipilih tidak valid.',
            'id_status_tabung.required' => 'Status tabung wajib dipilih.',
            'id_status_tabung.exists'   => 'Status tabung yang dipilih tidak valid.',
            'id_kepemilikan.required' => 'Status kepemilikan wajib dipilih.',
            'id_kepemilikan.exists'   => 'Status kepemilikan yang dipilih tidak valid.',
        ];

        $validator = Validator::make($request->all(), [
            'kode_tabung' => 'required|string|max:255|unique:tabungs',
            'id_jenis_tabung' => 'required|exists:jenis_tabungs,id_jenis_tabung',
            'id_status_tabung' => 'required|exists:status_tabungs,id_status_tabung',
            'id_kepemilikan' => 'required|exists:kepemilikans,id_kepemilikan',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'data'    => ['errors' => $validator->errors()]
            ], 422);
        }

        try {
            $tabung = Tabung::create($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Tabung berhasil ditambahkan.',
                'data'    => $tabung->load(['jenisTabung', 'statusTabung', 'kepemilikan'])
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    /**
     * Menampilkan satu tabung spesifik.
     */
    public function show($id)
    {
        try {
            $tabung = Tabung::with(['jenisTabung', 'statusTabung', 'kepemilikan'])->findOrFail($id);
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
     * Menampilkan satu tabung spesifik berdasarkan KODE (untuk scan QR).
     */
    public function showByKode($kode_tabung)
    {
        try {
            $tabung = Tabung::with(['jenisTabung', 'statusTabung', 'kepemilikan'])
                ->where('kode_tabung', $kode_tabung)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Detail tabung berhasil diambil.',
                'data'    => $tabung
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tabung dengan kode tersebut tidak ditemukan.',
                'data'    => null
            ], 404);
        }
    }

    /**
     * Memperbarui tabung yang ada.
     */
    public function update(Request $request, $id)
    {
        $messages = [
            'kode_tabung.required' => 'Kode tabung wajib diisi.',
            'kode_tabung.unique'   => 'Kode tabung ini sudah terdaftar.',
            'id_jenis_tabung.required' => 'Jenis tabung wajib dipilih.',
            'id_jenis_tabung.exists'   => 'Jenis tabung yang dipilih tidak valid.',
            'id_status_tabung.required' => 'Status tabung wajib dipilih.',
            'id_status_tabung.exists'   => 'Status tabung yang dipilih tidak valid.',
            'id_kepemilikan.required' => 'Status kepemilikan wajib dipilih.',
            'id_kepemilikan.exists'   => 'Status kepemilikan yang dipilih tidak valid.',
        ];

        $validator = Validator::make($request->all(), [
            'kode_tabung' => 'required|string|max:255|unique:tabungs,kode_tabung,' . $id . ',id_tabung',
            'id_jenis_tabung' => 'required|exists:jenis_tabungs,id_jenis_tabung',
            'id_status_tabung' => 'required|exists:status_tabungs,id_status_tabung',
        ], $messages);

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
