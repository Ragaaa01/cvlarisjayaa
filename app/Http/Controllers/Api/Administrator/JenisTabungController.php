<?php

namespace App\Http\Controllers\Api\Administrator;

use App\Http\Controllers\Controller;
use App\Models\JenisTabung; // Pastikan model ini menggunakan "use Illuminate\Database\Eloquent\SoftDeletes;"
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class JenisTabungController extends Controller
{
    /**
     * Menampilkan daftar semua jenis tabung.
     * Dapat memfilter berdasarkan status (misal: ?status=trashed untuk melihat data yang dihapus)
     */
    public function index(Request $request)
    {
        try {
            $query = JenisTabung::query();

            // Cek jika ada permintaan untuk melihat data yang sudah di-soft delete
            if ($request->query('status') === 'trashed') {
                $query->onlyTrashed();
            }

            $jenisTabungs = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Data jenis tabung berhasil diambil.',
                'data' => $jenisTabungs
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan jenis tabung baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_jenis' => 'required|string|max:255|unique:jenis_tabungs',
            'harga_pinjam' => 'required|numeric|min:0',
            'harga_isi_ulang' => 'required|numeric|min:0',
            'nilai_deposit' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $jenisTabung = JenisTabung::create($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Jenis tabung berhasil ditambahkan.',
                'data' => $jenisTabung
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan satu jenis tabung spesifik.
     */
    public function show($id)
    {
        try {
            // findOrFail secara default tidak akan menemukan data yang sudah di-soft delete
            $jenisTabung = JenisTabung::findOrFail($id);
            return response()->json([
                'success' => true,
                'message' => 'Detail jenis tabung berhasil diambil.',
                'data' => $jenisTabung
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ], 404);
        }
    }

    /**
     * Memperbarui jenis tabung yang ada.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_jenis' => 'required|string|max:255|unique:jenis_tabungs,nama_jenis,' . $id . ',id_jenis_tabung',
            'harga_pinjam' => 'required|numeric|min:0',
            'harga_isi_ulang' => 'required|numeric|min:0',
            'nilai_deposit' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $jenisTabung = JenisTabung::findOrFail($id);
            $jenisTabung->update($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Jenis tabung berhasil diperbarui.',
                'data' => $jenisTabung
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data atau data tidak ditemukan.'
            ], 500);
        }
    }

    /**
     * Memindahkan jenis tabung ke "tong sampah" (soft delete).
     */
    public function destroy($id)
    {
        try {
            $jenisTabung = JenisTabung::findOrFail($id);

            // Logika tambahan untuk mencegah penghapusan jika masih digunakan
            if ($jenisTabung->tabungs()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus: Jenis tabung masih terhubung dengan data tabung lain.'
                ], 400);
            }

            $jenisTabung->delete(); // Ini akan melakukan soft delete
            return response()->json([
                'success' => true,
                'message' => 'Jenis tabung berhasil dihapus (dipindahkan ke tong sampah).'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data atau data tidak ditemukan.'
            ], 500);
        }
    }

    /**
     * Mengembalikan jenis tabung yang telah di-soft delete.
     */
    public function restore($id)
    {
        try {
            $jenisTabung = JenisTabung::onlyTrashed()->findOrFail($id);
            $jenisTabung->restore();

            return response()->json([
                'success' => true,
                'message' => 'Jenis tabung berhasil dipulihkan.',
                'data' => $jenisTabung
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan: Data tidak ditemukan di tong sampah.'
            ], 404);
        }
    }

    /**
     * Menghapus jenis tabung secara permanen dari database.
     * Hanya bisa menghapus data yang sudah di-soft delete.
     */
    public function forceDestroy($id)
    {
        try {
            $jenisTabung = JenisTabung::onlyTrashed()->findOrFail($id);
            $jenisTabung->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Jenis tabung berhasil dihapus secara permanen.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus permanen: Data tidak ditemukan di tong sampah.'
            ], 404);
        }
    }
}
