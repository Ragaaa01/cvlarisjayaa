<?php

namespace App\Http\Controllers\Api\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Tabung;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PesananAdministratorController extends Controller
{
    /**
     * Mengambil daftar transaksi yang sudah lunas (valid) dan menunggu penyiapan.
     */
    public function getMenungguPenyiapan(Request $request)
    {
        try {
            $query = Transaksi::where('status_valid', true)
                // Kriteria utama: Cari transaksi yang memiliki detail...
                ->whereHas('transaksiDetails.tabung', function ($q) {
                    // ...di mana tabung yang terhubung memiliki id_status_tabung = 5 ('dipesan')
                    $q->where('id_status_tabung', 5);
                })
                ->with(['orang']); // Muat data pelanggan untuk ditampilkan di daftar

            // Logika pencarian
            if ($request->has('search')) {
                $query->whereHas('orang', function ($q) use ($request) {
                    $q->where('nama_lengkap', 'like', '%' . $request->search . '%');
                });
            }

            $pesanan = $query->latest()->paginate(15);

            return response()->json([
                'success' => true,
                'message' => 'Data pesanan menunggu penyiapan berhasil diambil.',
                'data'    => $pesanan
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data: ' . $e->getMessage()], 500);
        }
    }


    /**
     * [BARU] Menampilkan detail lengkap dari satu pesanan.
     */
    public function show($id_transaksi)
    {
        try {
            $transaksi = Transaksi::with([
                'orang.mitras',
                'transaksiDetails.jenisTransaksiDetail',
                'transaksiDetails.tabung.jenisTabung',
                'transaksiDetails.tabung.statusTabung'
            ])->findOrFail($id_transaksi);

            return response()->json([
                'success' => true,
                'message' => 'Detail pesanan berhasil diambil.',
                'data'    => $transaksi
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengambil detail pesanan.'], 404);
        }
    }


    /**
     * [DISEMPURNAKAN] Fungsi ini sekarang hanya mengonfirmasi penyiapan,
     * tidak lagi menetapkan tabung.
     */
    public function konfirmasiPenyiapan(Request $request, $id_transaksi)
    {
        DB::beginTransaction();
        try {
            $transaksi = Transaksi::findOrFail($id_transaksi);

            // Cari semua tabung yang 'dipesan' dalam transaksi ini
            $tabungDipesan = Tabung::whereHas('transaksiDetails', function ($q) use ($id_transaksi) {
                $q->where('id_transaksi', $id_transaksi);
            })->where('id_status_tabung', 5)->get(); // 5 = 'dipesan'

            if ($tabungDipesan->isEmpty()) {
                throw new Exception("Tidak ada tabung berstatus 'dipesan' untuk transaksi ini.");
            }

            // Ubah status semua tabung tersebut menjadi 'dipinjam'
            foreach ($tabungDipesan as $tabung) {
                $tabung->update(['id_status_tabung' => 2]); // 2 = 'dipinjam'
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Pesanan #{$id_transaksi} berhasil dikonfirmasi dan siap dikirim.",
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal mengonfirmasi pesanan: ' . $e->getMessage()], 500);
        }
    }
}
