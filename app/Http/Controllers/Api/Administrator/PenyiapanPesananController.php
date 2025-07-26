<?php

namespace App\Http\Controllers\Api\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\DetailPeminjaman;
use App\Models\Tabung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;

class PenyiapanPesananController extends Controller
{
    /**
     * Menampilkan daftar peminjaman yang menunggu untuk disiapkan (dialokasikan tabung fisiknya).
     */
    public function index()
    {
        try {
            $peminjamanMenunggu = Peminjaman::with(['akun.orang', 'detailPeminjamans.jenisTabung'])
                ->where('status_pinjam', true) // Hanya yang aktif
                ->whereHas('detailPeminjamans', function ($query) {
                    $query->whereNull('id_tabung'); // Di mana ada detail yang id_tabungnya masih kosong
                })
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data peminjaman yang menunggu penyiapan berhasil diambil.',
                'data' => $peminjamanMenunggu
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Menetapkan tabung fisik ke detail peminjaman.
     * Ini adalah inti dari proses penyiapan pesanan.
     */
    public function siapkan(Request $request, $id_peminjaman)
    {
        $validator = Validator::make($request->all(), [
            'alokasi' => 'required|array|min:1',
            'alokasi.*.id_detail_peminjaman' => 'required|exists:detail_peminjamans,id_detail_peminjaman',
            'alokasi.*.id_tabung' => 'required|exists:tabungs,id_tabung',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::findOrFail($id_peminjaman);

            foreach ($request->alokasi as $item) {
                $detail = DetailPeminjaman::where('id_peminjaman', $peminjaman->id_peminjaman)
                    ->where('id_detail_peminjaman', $item['id_detail_peminjaman'])
                    ->firstOrFail();

                $tabung = Tabung::findOrFail($item['id_tabung']);

                // Pastikan tabung tersedia untuk dipinjam
                if ($tabung->id_status_tabung != 1) { // Asumsi 1 = 'tersedia'
                    throw new Exception("Tabung dengan kode {$tabung->kode_tabung} tidak tersedia.");
                }

                // Pastikan jenis tabung sesuai dengan pesanan
                if ($tabung->id_jenis_tabung != $detail->id_jenis_tabung) {
                    throw new Exception("Jenis tabung fisik tidak sesuai dengan pesanan.");
                }

                // Update detail peminjaman dengan ID tabung fisik
                $detail->update(['id_tabung' => $tabung->id_tabung]);

                // Update status tabung menjadi 'dipinjam'
                $tabung->update(['id_status_tabung' => 2]); // Asumsi 2 = 'dipinjam'
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil disiapkan dan tabung telah dialokasikan.',
                'data' => $peminjaman->load('detailPeminjamans.tabung')
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyiapkan pesanan: ' . $e->getMessage()], 500);
        }
    }
}
