<?php

namespace App\Http\Controllers\Api\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Exception;
use Illuminate\Http\Request;

class NotifikasiPelangganController extends Controller
{
    /**
     * Mengambil daftar notifikasi milik pengguna yang sedang login,
     * diurutkan dari yang terbaru.
     */
    public function index(Request $request)
    {
        try {
            $akun = $request->user();
            $notifikasi = $akun->notifikasis()->latest()->paginate(20);

            return response()->json([
                'success' => true,
                'message' => 'Data notifikasi berhasil diambil.',
                'data'    => $notifikasi
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data notifikasi.'], 500);
        }
    }

    /**
     * Menandai satu notifikasi sebagai sudah dibaca.
     */
    public function tandaiDibaca(Request $request, $id_notifikasi)
    {
        try {
            $akun = $request->user();
            $notifikasi = Notifikasi::where('id_notifikasi', $id_notifikasi)
                ->where('id_akun', $akun->id_akun)
                ->firstOrFail();

            $notifikasi->update(['status_baca' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil ditandai sebagai dibaca.',
                'data'    => $notifikasi
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui status notifikasi.'], 500);
        }
    }
}
