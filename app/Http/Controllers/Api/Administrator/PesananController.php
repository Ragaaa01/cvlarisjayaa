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

class PesananController extends Controller
{
    /**
     * Mengambil daftar transaksi yang sudah lunas (valid)
     * dan memiliki item peminjaman yang belum ditetapkan tabung fisiknya.
     */
    public function getMenungguPenyiapan(Request $request)
    {
        try {
            $query = Transaksi::where('status_valid', true)
                ->whereHas('transaksiDetails', function ($q) {
                    $q->where('id_jenis_transaksi_detail', 1) // Hanya peminjaman
                        ->whereNull('id_tabung'); // Yang belum ditetapkan tabungnya
                })
                ->with(['orang', 'transaksiDetails' => function ($q) {
                    $q->where('id_jenis_transaksi_detail', 1)->whereNull('id_tabung');
                }]);

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
     * Menetapkan tabung fisik ke item transaksi detail.
     */
    public function siapkanPesanan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_transaksi_detail' => 'required|exists:transaksi_details,id_transaksi_detail',
            'kode_tabung' => 'required|exists:tabungs,kode_tabung',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'data' => ['errors' => $validator->errors()]], 422);
        }

        DB::beginTransaction();
        try {
            $detail = TransaksiDetail::findOrFail($request->id_transaksi_detail);
            $tabung = Tabung::where('kode_tabung', $request->kode_tabung)->firstOrFail();

            // Validasi: Pastikan tabung tersedia
            if ($tabung->id_status_tabung != 1) { // Asumsi 1 = 'tersedia'
                throw new Exception("Tabung dengan kode {$request->kode_tabung} tidak tersedia.");
            }

            // 1. Tetapkan tabung ke detail transaksi
            $detail->update(['id_tabung' => $tabung->id_tabung]);

            // 2. Ubah status tabung menjadi 'dipinjam'
            $tabung->update(['id_status_tabung' => 2]); // Asumsi 2 = 'dipinjam'

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Tabung {$tabung->kode_tabung} berhasil ditetapkan ke pesanan.",
                'data'    => $detail->load('tabung')
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyiapkan pesanan: ' . $e->getMessage()], 500);
        }
    }
}
