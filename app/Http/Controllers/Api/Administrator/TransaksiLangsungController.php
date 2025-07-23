<?php

namespace App\Http\Controllers\Api\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\Deposit;
use App\Models\JenisTabung;
use App\Models\Peminjaman;
use App\Models\DetailPeminjaman;
use App\Models\Pengisian;
use App\Models\DetailPengisian;
use App\Models\PembayaranTagihan;
use App\Models\RiwayatDeposit;
use App\Models\Tagihan;
use App\Models\Tabung;
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
        // 1. Validasi input yang lebih fleksibel
        $validator = Validator::make($request->all(), [
            'id_akun' => 'required|exists:akuns,id_akun',
            'items_pinjam' => 'required_without:items_isi_ulang|array',
            'items_pinjam.*.id_jenis_tabung' => 'sometimes|required_without:items_pinjam.*.kode_tabung|exists:jenis_tabungs,id_jenis_tabung',
            'items_pinjam.*.jumlah' => 'sometimes|required_with:items_pinjam.*.id_jenis_tabung|integer|min:1',
            'items_pinjam.*.kode_tabung' => 'sometimes|required_without:items_pinjam.*.id_jenis_tabung|exists:tabungs,kode_tabung',
            'items_isi_ulang' => 'required_without:items_pinjam|array',
            'items_isi_ulang.*.id_jenis_tabung' => 'sometimes|required_without:items_isi_ulang.*.kode_tabung|exists:jenis_tabungs,id_jenis_tabung',
            'items_isi_ulang.*.jumlah' => 'sometimes|required_with:items_isi_ulang.*.id_jenis_tabung|integer|min:1',
            'items_isi_ulang.*.kode_tabung' => 'sometimes|required_without:items_isi_ulang.*.id_jenis_tabung|exists:tabungs,kode_tabung',
            'metode_pembayaran' => 'required|in:tunai,transfer',
            'jumlah_dibayar' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // 2. Kalkulasi Biaya dan Kebutuhan Deposit dari semua item
            $totalBiayaAktual = 0;
            $totalKebutuhanDeposit = 0;
            $detailPinjamData = [];
            $detailIsiUlangData = [];

            // Kalkulasi untuk item peminjaman
            foreach ($request->input('items_pinjam', []) as $item) {
                if (isset($item['kode_tabung'])) {
                    $tabung = Tabung::where('kode_tabung', $item['kode_tabung'])->firstOrFail();
                    if ($tabung->id_status_tabung != 1) { // Asumsi 1 = 'tersedia'
                        throw new Exception("Tabung dengan kode {$item['kode_tabung']} tidak tersedia untuk dipinjam.");
                    }
                    $jenisTabung = $tabung->jenisTabung;
                    $totalBiayaAktual += $jenisTabung->harga_pinjam + $jenisTabung->harga_isi_ulang;
                    $totalKebutuhanDeposit += $jenisTabung->nilai_deposit;
                    $detailPinjamData[] = ['id_tabung' => $tabung->id_tabung, 'id_jenis_tabung' => $jenisTabung->id_jenis_tabung, 'harga' => $jenisTabung->harga_pinjam];
                } elseif (isset($item['id_jenis_tabung'])) {
                    $jenisTabung = JenisTabung::find($item['id_jenis_tabung']);
                    for ($i = 0; $i < $item['jumlah']; $i++) {
                        $totalBiayaAktual += $jenisTabung->harga_pinjam + $jenisTabung->harga_isi_ulang;
                        $totalKebutuhanDeposit += $jenisTabung->nilai_deposit;
                        $detailPinjamData[] = ['id_tabung' => null, 'id_jenis_tabung' => $jenisTabung->id_jenis_tabung, 'harga' => $jenisTabung->harga_pinjam];
                    }
                }
            }

            // Kalkulasi untuk item isi ulang
            foreach ($request->input('items_isi_ulang', []) as $item) {
                if (isset($item['kode_tabung'])) {
                    $tabung = Tabung::where('kode_tabung', $item['kode_tabung'])->firstOrFail();
                    $jenisTabung = $tabung->jenisTabung;
                    $totalBiayaAktual += $jenisTabung->harga_isi_ulang;
                    $detailIsiUlangData[] = ['id_tabung' => $tabung->id_tabung, 'id_jenis_tabung' => $jenisTabung->id_jenis_tabung, 'harga' => $jenisTabung->harga_isi_ulang];
                } elseif (isset($item['id_jenis_tabung'])) {
                    $jenisTabung = JenisTabung::find($item['id_jenis_tabung']);
                    for ($i = 0; $i < $item['jumlah']; $i++) {
                        $totalBiayaAktual += $jenisTabung->harga_isi_ulang;
                        $detailIsiUlangData[] = ['id_tabung' => null, 'id_jenis_tabung' => $jenisTabung->id_jenis_tabung, 'harga' => $jenisTabung->harga_isi_ulang];
                    }
                }
            }

            // 3. Cek Deposit, Verifikasi Pembayaran, Buat Tagihan, Top-up Deposit
            $akun = Akun::findOrFail($request->id_akun);
            $deposit = $akun->deposit ?? Deposit::create(['id_akun' => $akun->id_akun, 'saldo' => 0]);
            $depositTopUp = max(0, $totalKebutuhanDeposit - $deposit->saldo);
            $totalYangHarusDibayar = $totalBiayaAktual + $depositTopUp;
            if ($request->jumlah_dibayar < $totalYangHarusDibayar) {
                throw new Exception("Jumlah pembayaran tidak mencukupi. Dibutuhkan: " . number_format($totalYangHarusDibayar));
            }
            $tagihan = null;
            if ($totalBiayaAktual > 0) {
                $tagihan = Tagihan::create(['id_akun' => $akun->id_akun, 'total_tagihan' => $totalBiayaAktual, 'jumlah_dibayar' => $totalBiayaAktual, 'sisa' => 0, 'status_tagihan' => 'lunas']);
                PembayaranTagihan::create(['id_tagihan' => $tagihan->id_tagihan, 'jumlah_dibayar' => $totalBiayaAktual, 'metode_pembayaran' => $request->metode_pembayaran]);
            }
            if ($depositTopUp > 0) {
                $deposit->increment('saldo', $depositTopUp);
                RiwayatDeposit::create(['id_deposit' => $deposit->id_deposit, 'jenis_aktivitas' => 'top_up', 'jumlah' => $depositTopUp, 'keterangan' => 'Top-up untuk transaksi baru.']);
            }

            // 7. Buat Catatan Peminjaman dan Pengisian beserta detailnya
            $peminjaman = null;
            if (!empty($detailPinjamData)) {
                $peminjaman = Peminjaman::create(['id_akun' => $akun->id_akun, 'id_tagihan' => optional($tagihan)->id_tagihan, 'tanggal_pinjam' => now(), 'tanggal_aktivitas_terakhir' => now(), 'status_pinjam' => true]);

                foreach ($detailPinjamData as $detail) {
                    DetailPeminjaman::create([
                        'id_peminjaman' => $peminjaman->id_peminjaman,
                        'id_jenis_tabung' => $detail['id_jenis_tabung'],
                        'id_tabung' => $detail['id_tabung'],
                        'harga_pinjam_saat_itu' => $detail['harga'],
                    ]);
                    if ($detail['id_tabung']) {
                        Tabung::find($detail['id_tabung'])->update(['id_status_tabung' => 2]); // Asumsi 2 = 'dipinjam'
                    }
                }
            }

            $pengisian = null;
            if (!empty($detailIsiUlangData)) {
                $biayaIsiUlangSaja = collect($detailIsiUlangData)->sum('harga');
                $pengisian = Pengisian::create(['id_akun' => $akun->id_akun, 'id_tagihan' => optional($tagihan)->id_tagihan, 'total_biaya' => $biayaIsiUlangSaja, 'waktu_transaksi' => now()]);

                foreach ($detailIsiUlangData as $detail) {
                    $detailPengisian = DetailPengisian::create(['id_pengisian' => $pengisian->id_pengisian, 'id_tabung' => $detail['id_tabung'], 'id_jenis_tabung' => $detail['id_jenis_tabung'], 'harga_pengisian_saat_itu' => $detail['harga']]);
                }
            }
            if ($detailPengisian->id_tabung) {
                // Cari peminjaman aktif yang berisi tabung ini
                $detailPeminjaman = DetailPeminjaman::where('id_tabung', $detailPengisian->id_tabung)
                    ->whereHas('peminjaman', function ($q) {
                        $q->where('status_pinjam', true);
                    })->first();

                if ($detailPeminjaman) {
                    // Update tanggal aktivitas terakhirnya
                    $detailPeminjaman->peminjaman->update(['tanggal_aktivitas_terakhir' => now()]);
                }
            }

            DB::commit();

            if ($peminjaman) $peminjaman->load('detailPeminjamans');
            if ($pengisian) $pengisian->load('detailPengisians');

            return response()->json([
                'success' => true,
                'message' => 'Transaksi gabungan berhasil dibuat.',
                'data' => [
                    'peminjaman' => $peminjaman,
                    'pengisian' => $pengisian,
                    'tagihan' => $tagihan,
                    'detail_pembayaran' => ['total_biaya_aktual' => $totalBiayaAktual, 'top_up_deposit' => $depositTopUp, 'total_dibayar' => $request->jumlah_dibayar]
                ]
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat membuat transaksi: ' . $e->getMessage()], 500);
        }
    }
}
