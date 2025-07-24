<?php

namespace App\Http\Controllers\Api\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\DetailPeminjaman;
use App\Models\JenisTabung;
use App\Models\Peminjaman;
use App\Models\Pengisian;
use App\Models\DetailPengisian; // <-- [PENTING] Tambahkan ini
use App\Models\Tagihan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Midtrans\Config;
use Midtrans\Snap;

class PesananController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function buatPesanan(Request $request)
    {
        // [PERBAIKAN 1] Validator diperbarui untuk menerima id_tabung
        $validator = Validator::make($request->all(), [
            'items_pinjam' => 'required_without:items_isi_ulang|array',
            'items_pinjam.*.id_jenis_tabung' => 'required|exists:jenis_tabungs,id_jenis_tabung',
            'items_pinjam.*.jumlah' => 'required|integer|min:1',
            'items_isi_ulang' => 'required_without:items_pinjam|array',
            'items_isi_ulang.*.id_jenis_tabung' => 'sometimes|required_without:items_isi_ulang.*.id_tabung|exists:jenis_tabungs,id_jenis_tabung',
            'items_isi_ulang.*.jumlah' => 'sometimes|required_with:items_isi_ulang.*.id_jenis_tabung|integer|min:1',
            'items_isi_ulang.*.id_tabung' => 'sometimes|required_without:items_isi_ulang.*.id_jenis_tabung|exists:tabungs,id_tabung',
            'metode_pembayaran' => 'required|in:tunai,transfer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $akun = $request->user();

            $totalBiayaAktual = 0;
            $totalKebutuhanDeposit = 0;
            $itemsPinjam = $request->input('items_pinjam', []);
            $itemsIsiUlang = $request->input('items_isi_ulang', []);

            // ... (Logika kalkulasi peminjaman tetap sama)
            foreach ($itemsPinjam as $item) {
                $jenisTabung = JenisTabung::find($item['id_jenis_tabung']);
                $totalBiayaAktual += ($jenisTabung->harga_pinjam + $jenisTabung->harga_isi_ulang) * $item['jumlah'];
                $totalKebutuhanDeposit += $jenisTabung->nilai_deposit * $item['jumlah'];
            }

            // [PERBAIKAN 2] Logika kalkulasi isi ulang yang lebih cerdas
            foreach ($itemsIsiUlang as $item) {
                $jenisTabung = null;
                if (isset($item['id_tabung'])) {
                    // Verifikasi bahwa tabung ini memang sedang dipinjam oleh user
                    $detailPinjam = DetailPeminjaman::where('id_tabung', $item['id_tabung'])
                        ->whereHas('peminjaman', function ($query) use ($akun) {
                            $query->where('id_akun', $akun->id_akun)->where('status_pinjam', true);
                        })->first();

                    if (!$detailPinjam) {
                        throw new Exception("Tabung dengan ID {$item['id_tabung']} tidak sedang Anda pinjam.");
                    }
                    $jenisTabung = $detailPinjam->jenisTabung;
                } else {
                    $jenisTabung = JenisTabung::find($item['id_jenis_tabung']);
                }
                $jumlah = $item['jumlah'] ?? 1;
                $totalBiayaAktual += $jenisTabung->harga_isi_ulang * $jumlah;
            }

            // ... (Logika deposit, tagihan, dan peminjaman tetap sama)
            $saldoDepositSaatIni = $akun->deposit ? $akun->deposit->saldo : 0;
            $depositTopUp = max(0, $totalKebutuhanDeposit - $saldoDepositSaatIni);
            $totalPembayaran = $totalBiayaAktual + $depositTopUp;
            $tagihan = Tagihan::create(['id_akun' => $akun->id_akun, 'total_tagihan' => $totalPembayaran, 'jumlah_biaya_aktual' => $totalBiayaAktual, 'jumlah_top_up' => $depositTopUp, 'sisa' => $totalPembayaran, 'status_tagihan' => 'belum_lunas']);
            if (!empty($itemsPinjam)) {
                $peminjaman = $akun->peminjamans()->create(['id_tagihan' => $tagihan->id_tagihan, 'tanggal_pinjam' => now(), 'tanggal_aktivitas_terakhir' => now(), 'status_pinjam' => false]);
                foreach ($itemsPinjam as $item) {
                    $jenisTabung = JenisTabung::find($item['id_jenis_tabung']);
                    for ($i = 0; $i < $item['jumlah']; $i++) {
                        $peminjaman->detailPeminjamans()->create(['id_jenis_tabung' => $item['id_jenis_tabung'], 'id_tabung' => null, 'harga_pinjam_saat_itu' => $jenisTabung->harga_pinjam]);
                    }
                }
            }

            // [PERBAIKAN 3] Logika pembuatan pengisian dan update tanggal aktivitas
            if (!empty($itemsIsiUlang)) {
                $biayaIsiUlangSaja = 0; // Kalkulasi ulang hanya untuk pengisian
                foreach ($itemsIsiUlang as $item) {
                    $jenisTabung = isset($item['id_tabung']) ? DetailPeminjaman::where('id_tabung', $item['id_tabung'])->first()->jenisTabung : JenisTabung::find($item['id_jenis_tabung']);
                    $jumlah = $item['jumlah'] ?? 1;
                    $biayaIsiUlangSaja += $jenisTabung->harga_isi_ulang * $jumlah;
                }

                $pengisian = $akun->pengisians()->create(['id_tagihan' => $tagihan->id_tagihan, 'total_biaya' => $biayaIsiUlangSaja, 'waktu_transaksi' => now()]);

                foreach ($itemsIsiUlang as $item) {
                    if (isset($item['id_tabung'])) {
                        $detailPinjam = DetailPeminjaman::where('id_tabung', $item['id_tabung'])->whereHas('peminjaman', function ($q) use ($akun) {
                            $q->where('status_pinjam', true)->where('id_akun', $akun->id_akun);
                        })->firstOrFail();
                        $pengisian->detailPengisians()->create(['id_tabung' => $item['id_tabung'], 'id_jenis_tabung' => $detailPinjam->id_jenis_tabung, 'harga_pengisian_saat_itu' => $detailPinjam->jenisTabung->harga_isi_ulang]);
                        // Update tanggal aktivitas terakhir dari peminjaman terkait
                        $detailPinjam->peminjaman->update(['tanggal_aktivitas_terakhir' => now()]);
                    } else {
                        $jenisTabung = JenisTabung::find($item['id_jenis_tabung']);
                        for ($i = 0; $i < $item['jumlah']; $i++) {
                            $pengisian->detailPengisians()->create(['id_tabung' => null, 'id_jenis_tabung' => $item['id_jenis_tabung'], 'harga_pengisian_saat_itu' => $jenisTabung->harga_isi_ulang]);
                        }
                    }
                }
            }

            // ... (Logika Midtrans tetap sama)
            $paymentUrl = null;
            $message = 'Pesanan berhasil dibuat. Silakan lakukan pembayaran saat pengambilan barang.';
            if ($request->metode_pembayaran == 'transfer') {
                if ($totalPembayaran > 0) {
                    $itemDetails = [];
                    if ($totalBiayaAktual > 0) $itemDetails[] = ['id' => 'BIAYA_LAYANAN', 'price' => $totalBiayaAktual, 'quantity' => 1, 'name' => 'Biaya Sewa & Isi Ulang'];
                    if ($depositTopUp > 0) $itemDetails[] = ['id' => 'TOPUP_DEPOSIT', 'price' => $depositTopUp, 'quantity' => 1, 'name' => 'Top Up Saldo Deposit'];
                    $midtransParams = ['transaction_details' => ['order_id' => 'TRX-' . $tagihan->id_tagihan . '-' . time(), 'gross_amount' => $totalPembayaran], 'customer_details' => ['first_name' => $akun->orang->nama_lengkap, 'email' => $akun->email, 'phone' => $akun->orang->no_telepon], 'item_details' => $itemDetails, 'callbacks' => [
                        'finish' => 'https://myapp.com/finish'
                    ]];
                    $paymentUrl = Snap::createTransaction($midtransParams)->redirect_url;
                    $message = 'Pesanan berhasil dibuat. Silakan lanjutkan ke pembayaran.';
                } else {
                    $tagihan->update(['status_tagihan' => 'lunas', 'sisa' => 0]);
                    Peminjaman::where('id_tagihan', $tagihan->id_tagihan)->update(['status_pinjam' => true]);
                    $message = 'Pesanan gratis Anda berhasil dibuat dan akan segera diproses.';
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => $message, 'data' => ['tagihan' => $tagihan, 'payment_url' => $paymentUrl]], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal membuat pesanan: ' . $e->getMessage()], 500);
        }
    }
}
