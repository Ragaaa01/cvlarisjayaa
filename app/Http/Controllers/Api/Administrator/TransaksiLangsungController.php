<?php

namespace App\Http\Controllers\Api\Administrator;

use App\Http\Controllers\Controller;
use App\Models\JenisTabung;
use App\Models\Orang;
use App\Models\Pembayaran;
use App\Models\Tabung;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;

class TransaksiLangsungController extends Controller
{
    /**
     * Membuat transaksi gabungan (peminjaman dan/atau isi ulang) untuk pelanggan langsung.
     * Mampu menangani input manual (jenis & jumlah) dan hasil scan QR (kode_tabung).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_orang' => 'required|exists:orangs,id_orang',
            'items_pinjam' => 'sometimes|array',
            'items_pinjam.*.kode_tabung' => 'required|exists:tabungs,kode_tabung',
            'items_isi_ulang' => 'sometimes|array',
            'items_isi_ulang.*.kode_tabung' => 'required|exists:tabungs,kode_tabung',
            'pembayaran' => 'required|array',
            'pembayaran.jumlah_pembayaran' => 'required|numeric|min:0',
            'pembayaran.metode_pembayaran' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'data' => ['errors' => $validator->errors()]], 422);
        }

        DB::beginTransaction();
        try {
            $orang = Orang::findOrFail($request->id_orang);
            $totalTransaksi = 0;
            $detailItems = [];

            // 1. Proses Item Peminjaman
            foreach ($request->input('items_pinjam', []) as $item) {
                $tabung = Tabung::where('kode_tabung', $item['kode_tabung'])->firstOrFail();

                // Validasi: Tabung untuk dipinjam harus berstatus 'tersedia'
                if ($tabung->statusTabung->status_tabung !== 'tersedia') {
                    throw new Exception("Tabung {$item['kode_tabung']} tidak tersedia untuk dipinjam.");
                }

                $jenisTabung = $tabung->jenisTabung;
                // a. Detail untuk Harga Pinjam
                if ($jenisTabung->harga_pinjam > 0) {
                    $detailItems[] = ['id_tabung' => $tabung->id_tabung, 'jenis' => 'peminjaman', 'harga' => $jenisTabung->harga_pinjam];
                    $totalTransaksi += $jenisTabung->harga_pinjam;
                }

                // b. Detail untuk Harga Isi Ulang Pertama
                $detailItems[] = ['id_tabung' => $tabung->id_tabung, 'jenis' => 'isi_ulang', 'harga' => $jenisTabung->harga_isi_ulang];
                $totalTransaksi += $jenisTabung->harga_isi_ulang;

                // c. Detail untuk Deposit
                $detailItems[] = ['id_tabung' => $tabung->id_tabung, 'jenis' => 'deposit', 'harga' => $jenisTabung->nilai_deposit];
                $totalTransaksi += $jenisTabung->nilai_deposit;
            }

            // 2. Proses Item Isi Ulang
            foreach ($request->input('items_isi_ulang', []) as $item) {
                $tabung = Tabung::where('kode_tabung', $item['kode_tabung'])->firstOrFail();
                $jenisTabung = $tabung->jenisTabung;
                $biayaIsiUlang = $jenisTabung->harga_isi_ulang;

                $detailItems[] = ['id_tabung' => $tabung->id_tabung, 'jenis' => 'isi_ulang', 'harga' => $biayaIsiUlang];
                $totalTransaksi += $biayaIsiUlang;
            }

            if (empty($detailItems)) {
                throw new Exception("Tidak ada item transaksi yang diproses.");
            }

            // 3. Buat Transaksi Induk
            $transaksi = Transaksi::create([
                'id_orang' => $orang->id_orang,
                'total_transaksi' => $totalTransaksi,
                'status_valid' => true,
                'tanggal_transaksi' => now()->toDateString(),
                'waktu_transaksi' => now()->toTimeString(),
            ]);

            // 4. Buat Detail Transaksi
            foreach ($detailItems as $detail) {
                // Asumsi ID jenis transaksi: 1=peminjaman, 2=isi_ulang, 3=deposit
                $idJenis = null;
                if ($detail['jenis'] === 'peminjaman') $idJenis = 1;
                if ($detail['jenis'] === 'isi_ulang') $idJenis = 2;
                if ($detail['jenis'] === 'deposit') $idJenis = 3;

                TransaksiDetail::create([
                    'id_transaksi' => $transaksi->id_transaksi,
                    'id_tabung' => $detail['id_tabung'],
                    'id_jenis_transaksi_detail' => $idJenis,
                    'harga' => $detail['harga'],
                ]);
            }

            // 5. Catat Pembayaran
            $pembayaranData = $request->pembayaran;
            $totalUtangSaatIni = (Transaksi::where('id_orang', $orang->id_orang)->sum('total_transaksi')) - (Pembayaran::where('id_orang', $orang->id_orang)->sum('jumlah_pembayaran'));

            Pembayaran::create([
                'id_orang' => $orang->id_orang,
                'total_transaksi' => $totalUtangSaatIni,
                'jumlah_pembayaran' => $pembayaranData['jumlah_pembayaran'],
                'metode_pembayaran' => $pembayaranData['metode_pembayaran'],
                'nomor_referensi' => 'TUNAI-' . time(), // Referensi internal
                'tanggal_pembayaran' => now()->toDateString(),
                'waktu_pembayaran' => now()->toTimeString(),
            ]);

            // 6. Update Status Tabung yang Dipinjam
            foreach ($request->input('items_pinjam', []) as $item) {
                Tabung::where('kode_tabung', $item['kode_tabung'])->update(['id_status_tabung' => 2]); // Asumsi 2 = 'dipinjam'
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibuat.',
                'data'    => $transaksi->load('transaksiDetails.tabung')
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat transaksi: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }
}
