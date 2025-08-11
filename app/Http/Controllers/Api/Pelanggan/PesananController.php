<?php

namespace App\Http\Controllers\Api\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\JenisTabung;
use App\Models\Tabung;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
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
        // Set konfigurasi Midtrans saat controller diinisialisasi
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'items' => 'required|array|min:1',
    //         'items.*.id_jenis_tabung' => 'required|exists:jenis_tabungs,id_jenis_tabung',
    //         'items.*.tipe' => 'required|string|in:peminjaman,isi_ulang',
    //         'items.*.jumlah' => 'required|integer|min:1',
    //     ]);

    //     if ($validator->fails()) {
    //         // [PERBAIKAN] Menggunakan format respons JSON standar untuk error validasi
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validasi gagal.',
    //             'data'    => ['errors' => $validator->errors()]
    //         ], 422);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $akun = $request->user();
    //         $orang = $akun->orang;
    //         $totalTransaksi = 0;
    //         $detailItemsForMidtrans = [];

    //         // Buat transaksi induk terlebih dahulu
    //         $transaksi = Transaksi::create([
    //             'id_orang' => $orang->id_orang,
    //             'total_transaksi' => 0, // Akan diupdate nanti
    //             'status_valid' => false, // Menunggu pembayaran
    //             'tanggal_transaksi' => now()->toDateString(),
    //             'waktu_transaksi' => now()->toTimeString(),
    //         ]);

    //         foreach ($request->items as $item) {
    //             $jenisTabung = JenisTabung::find($item['id_jenis_tabung']);

    //             if ($item['tipe'] === 'peminjaman') {
    //                 // Cek ketersediaan stok
    //                 $tabungDipesan = Tabung::where('id_jenis_tabung', $item['id_jenis_tabung'])
    //                     ->where('id_status_tabung', 1) // 1 = 'tersedia'
    //                     ->lockForUpdate() // Kunci baris untuk mencegah race condition
    //                     ->take($item['jumlah'])
    //                     ->get();
    //                 if ($tabungDipesan->count() < $item['jumlah']) {
    //                     throw new Exception("Stok untuk {$jenisTabung->nama_jenis} tidak mencukupi.");
    //                 }
    //                 // Tambahkan 3 detail: peminjaman, isi ulang pertama, dan deposit
    //                 $biayaPinjam = $item['jumlah'] * $jenisTabung->harga_pinjam;
    //                 $biayaIsiUlang = $item['jumlah'] * $jenisTabung->harga_isi_ulang;
    //                 $biayaDeposit = $item['jumlah'] * $jenisTabung->nilai_deposit;

    //                 $transaksi->transaksiDetails()->createMany([
    //                     ['id_jenis_transaksi_detail' => 1, 'harga' => $biayaPinjam], // Peminjaman
    //                     ['id_jenis_transaksi_detail' => 2, 'harga' => $biayaIsiUlang], // Isi Ulang
    //                     ['id_jenis_transaksi_detail' => 3, 'harga' => $biayaDeposit], // Deposit
    //                 ]);

    //                 $totalTransaksi += $biayaPinjam + $biayaIsiUlang + $biayaDeposit;
    //                 $detailItemsForMidtrans[] = ['id' => 'P-' . $jenisTabung->id_jenis_tabung, 'price' => $jenisTabung->harga_pinjam + $jenisTabung->harga_isi_ulang + $jenisTabung->nilai_deposit, 'quantity' => $item['jumlah'], 'name' => 'Pinjam ' . $jenisTabung->nama_jenis];
    //             } else { // tipe 'isi_ulang'
    //                 $biayaIsiUlang = $item['jumlah'] * $jenisTabung->harga_isi_ulang;
    //                 $transaksi->transaksiDetails()->create(['id_jenis_transaksi_detail' => 2, 'harga' => $biayaIsiUlang]);
    //                 $totalTransaksi += $biayaIsiUlang;
    //                 $detailItemsForMidtrans[] = ['id' => 'I-' . $jenisTabung->id_jenis_tabung, 'price' => $jenisTabung->harga_isi_ulang, 'quantity' => $item['jumlah'], 'name' => 'Isi Ulang ' . $jenisTabung->nama_jenis];
    //             }
    //         }

    //         // Update total transaksi
    //         $transaksi->update(['total_transaksi' => $totalTransaksi]);

    //         // Buat transaksi Midtrans
    //         $orderId = 'TRX-' . $transaksi->id_transaksi . '-' . time();
    //         $midtransParams = [
    //             'transaction_details' => ['order_id' => $orderId, 'gross_amount' => $totalTransaksi],
    //             // [PERBAIKAN] Mengisi detail pelanggan dengan data yang ada
    //             'customer_details' => [
    //                 'first_name' => $orang->nama_lengkap,
    //                 'email' => $akun->email,
    //                 'phone' => $orang->no_telepon,
    //             ],
    //             'item_details' => $detailItemsForMidtrans,
    //             'callbacks' => ['finish' => 'https://myapp.com/finish']
    //         ];
    //         $paymentUrl = Snap::createTransaction($midtransParams)->redirect_url;

    //         DB::commit();

    //         // Menggunakan format respons JSON standar untuk sukses
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Pesanan berhasil dibuat.',
    //             'data'    => ['payment_url' => $paymentUrl]
    //         ], 201);
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         // Menggunakan format respons JSON standar untuk error
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage(),
    //             'data'    => null
    //         ], 500);
    //     }
    // }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.id_jenis_tabung' => 'required|exists:jenis_tabungs,id_jenis_tabung',
            'items.*.tipe' => 'required|string|in:peminjaman,isi_ulang',
            'items.*.jumlah' => 'required|integer|min:1',
            'metode_pembayaran' => 'required|string|in:transfer,tunai',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'data'    => ['errors' => $validator->errors()]
            ], 422);
        }

        DB::beginTransaction();
        try {
            $akun = $request->user();
            $orang = $akun->orang;
            $metodePembayaran = $request->input('metode_pembayaran');
            $totalTransaksi = 0;
            $detailItemsForMidtrans = [];
            $detailTransaksiData = [];

            $transaksi = Transaksi::create([
                'id_orang' => $orang->id_orang,
                'total_transaksi' => 0,
                'status_valid' => $metodePembayaran === 'tunai' ? true : false,
                'tanggal_transaksi' => now()->toDateString(),
                'waktu_transaksi' => now()->toTimeString(),
            ]);

            foreach ($request->items as $item) {
                $jenisTabung = JenisTabung::find($item['id_jenis_tabung']);

                if ($item['tipe'] === 'peminjaman') {
                    for ($i = 0; $i < $item['jumlah']; $i++) {
                        $tabungUntukDipesan = Tabung::where('id_jenis_tabung', $item['id_jenis_tabung'])
                            ->where('id_status_tabung', 1)
                            ->lockForUpdate()
                            ->first();

                        if (!$tabungUntukDipesan) {
                            throw new Exception("Stok untuk {$jenisTabung->nama_jenis} tidak mencukupi.");
                        }

                        $tabungUntukDipesan->update(['id_status_tabung' => 5]);

                        $detailTransaksiData[] = ['id_jenis_transaksi_detail' => 1, 'id_tabung' => $tabungUntukDipesan->id_tabung, 'harga' => $jenisTabung->harga_pinjam];
                        $detailTransaksiData[] = ['id_jenis_transaksi_detail' => 2, 'id_tabung' => $tabungUntukDipesan->id_tabung, 'harga' => $jenisTabung->harga_isi_ulang];
                        $detailTransaksiData[] = ['id_jenis_transaksi_detail' => 3, 'id_tabung' => $tabungUntukDipesan->id_tabung, 'harga' => $jenisTabung->nilai_deposit];

                        $totalTransaksi += $jenisTabung->harga_pinjam + $jenisTabung->harga_isi_ulang + $jenisTabung->nilai_deposit;
                    }
                    $detailItemsForMidtrans[] = ['id' => 'P-' . $jenisTabung->id_jenis_tabung, 'price' => $jenisTabung->harga_pinjam + $jenisTabung->harga_isi_ulang + $jenisTabung->nilai_deposit, 'quantity' => $item['jumlah'], 'name' => 'Pinjam ' . $jenisTabung->nama_jenis];
                } else {
                    for ($i = 0; $i < $item['jumlah']; $i++) {
                        $detailTransaksiData[] = ['id_jenis_transaksi_detail' => 2, 'id_tabung' => null, 'harga' => $jenisTabung->harga_isi_ulang];
                    }
                    $totalTransaksi += $item['jumlah'] * $jenisTabung->harga_isi_ulang;
                    $detailItemsForMidtrans[] = ['id' => 'I-' . $jenisTabung->id_jenis_tabung, 'price' => $jenisTabung->harga_isi_ulang, 'quantity' => $item['jumlah'], 'name' => 'Isi Ulang ' . $jenisTabung->nama_jenis];
                }
            }

            $transaksi->transaksiDetails()->createMany($detailTransaksiData);
            $transaksi->update(['total_transaksi' => $totalTransaksi]);

            if ($metodePembayaran === 'transfer') {

                $orderId = 'TRX-' . $transaksi->id_transaksi . '-' . time();
                $midtransParams = [
                    'transaction_details' => ['order_id' => $orderId, 'gross_amount' => $totalTransaksi],
                    'customer_details' => ['first_name' => $orang->nama_lengkap, 'email' => $akun->email, 'phone' => $orang->no_telepon,],
                    'item_details' => $detailItemsForMidtrans,
                    'callbacks' => ['finish' => 'https://myapp.com/finish']
                ];
                $paymentUrl = Snap::createTransaction($midtransParams)->redirect_url;
                $transaksi->update(['payment_url' => $paymentUrl]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Pesanan berhasil dibuat. Silakan selesaikan pembayaran.',
                    'data'    => ['payment_url' => $paymentUrl]
                ], 201);
            } else {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Pesanan Anda berhasil dibuat dan akan segera disiapkan. Silakan lakukan pembayaran saat pengiriman.',
                    'data'    => null
                ], 201);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pesanan: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }
}
