<?php

namespace App\Http\Controllers\Api\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Mitra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class MitraController extends Controller
{
    /**
     * Menampilkan daftar semua mitra dengan fitur pencarian dan filter.
     */
    public function index(Request $request)
    {
        try {
            $query = Mitra::with(['kelurahan.kecamatan.kabupaten.provinsi', 'orangs']);

            // Filter berdasarkan status verifikasi (aktif/nonaktif)
            if ($request->has('verified')) {
                $query->where('verified', $request->boolean('verified'));
            }

            // Pencarian berdasarkan nama mitra, nama perwakilan, atau wilayah
            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('nama_mitra', 'like', '%' . $searchTerm . '%')
                        ->orWhereHas('orangs', function ($subQ) use ($searchTerm) {
                            $subQ->where('nama_lengkap', 'like', '%' . $searchTerm . '%');
                        })
                        ->orWhereHas('kelurahan', function ($subQ) use ($searchTerm) {
                            $subQ->where('nama_kelurahan', 'like', '%' . $searchTerm . '%');
                        });
                });
            }

            $mitras = $query->latest()->paginate(15);

            return response()->json([
                'success' => true,
                'message' => 'Data mitra berhasil diambil.',
                'data'    => $mitras
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data mitra: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    /**
     * Menyimpan data mitra baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_mitra' => 'required|string|max:255|unique:mitras,nama_mitra',
            'id_kelurahan' => 'nullable|exists:kelurahans,id_kelurahan',
            'alamat_mitra' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'data' => ['errors' => $validator->errors()]], 422);
        }

        try {
            $mitra = Mitra::create($request->all());
            return response()->json(['success' => true, 'message' => 'Mitra baru berhasil ditambahkan.', 'data' => $mitra], 201);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan data mitra.', 'data' => null], 500);
        }
    }

    /**
     * Menampilkan detail satu mitra, termasuk orang yang terhubung.
     */
    public function show($id_mitra)
    {
        try {
            $mitra = Mitra::with(['orangs', 'kelurahan.kecamatan.kabupaten.provinsi'])->findOrFail($id_mitra);
            return response()->json(['success' => true, 'message' => 'Detail mitra berhasil diambil.', 'data' => $mitra], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Mitra tidak ditemukan.', 'data' => null], 404);
        }
    }

    /**
     * Memperbarui data mitra yang ada.
     */
    public function update(Request $request, $id_mitra)
    {
        $validator = Validator::make($request->all(), [
            'nama_mitra' => 'required|string|max:255|unique:mitras,nama_mitra,' . $id_mitra . ',id_mitra',
            'id_kelurahan' => 'nullable|exists:kelurahans,id_kelurahan',
            'alamat_mitra' => 'nullable|string',
            'verified' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'data' => ['errors' => $validator->errors()]], 422);
        }

        try {
            $mitra = Mitra::findOrFail($id_mitra);
            $mitra->update($request->all());
            return response()->json(['success' => true, 'message' => 'Data mitra berhasil diperbarui.', 'data' => $mitra], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui data mitra.', 'data' => null], 500);
        }
    }

    /**
     * Menghapus data mitra.
     */
    public function destroy($id_mitra)
    {
        try {
            $mitra = Mitra::findOrFail($id_mitra);
            // Mencegah penghapusan jika masih ada orang yang terhubung
            if ($mitra->orangs()->exists()) {
                return response()->json(['success' => false, 'message' => 'Gagal menghapus: Mitra masih memiliki perwakilan terdaftar.'], 409);
            }
            $mitra->delete();
            return response()->json(['success' => true, 'message' => 'Mitra berhasil dihapus.'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus data mitra.', 'data' => null], 500);
        }
    }

    /**
     * [BARU] Memverifikasi mitra dan mengubah status 'verified' menjadi true.
     */
    public function verify($id_mitra)
    {
        try {
            $mitra = Mitra::findOrFail($id_mitra);

            if ($mitra->verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mitra ini sudah terverifikasi.',
                    'data'    => null
                ], 409); // 409 Conflict
            }

            $mitra->update(['verified' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Mitra berhasil diverifikasi.',
                'data'    => $mitra
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memverifikasi mitra atau data tidak ditemukan.',
                'data'    => null
            ], 500);
        }
    }
}
