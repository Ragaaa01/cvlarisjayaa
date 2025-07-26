<?php

namespace App\Http\Controllers\Api\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Midtrans\Config;
use Midtrans\Snap;
use Exception;

class DepositController extends Controller
{
    public function __construct()
    {
        // Set konfigurasi Midtrans saat controller diinisialisasi
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Membuat tagihan untuk top-up deposit dan menghasilkan Payment URL Midtrans.
     */
    public function topUp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jumlah' => 'required|numeric|min:10000', // Minimal top-up 10,000
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $akun = $request->user();
            $jumlahTopUp = $request->jumlah;

            // 1. Buat tagihan baru khusus untuk top-up
            $tagihan = Tagihan::create([
                'id_akun' => $akun->id_akun,
                'total_tagihan' => $jumlahTopUp,
                'jumlah_biaya_aktual' => 0, // Tidak ada biaya aktual untuk top-up
                'jumlah_top_up' => $jumlahTopUp, // Seluruhnya adalah top-up
                'sisa' => $jumlahTopUp,
                'status_tagihan' => 'belum_lunas',
            ]);

            // 2. Siapkan parameter untuk Midtrans
            $midtransParams = [
                'transaction_details' => [
                    'order_id' => 'TOPUP-' . $tagihan->id_tagihan . '-' . time(),
                    'gross_amount' => $jumlahTopUp,
                ],
                'customer_details' => [
                    'first_name' => $akun->orang->nama_lengkap,
                    'email' => $akun->email,
                    'phone' => $akun->orang->no_telepon,
                ],
                'item_details' => [
                    [
                        'id' => 'DEPOSIT01',
                        'price' => $jumlahTopUp,
                        'quantity' => 1,
                        'name' => 'Top Up Saldo Deposit',
                    ],
                ],
                // --- [PERBAIKAN DI SINI] ---
                // Beritahu Midtrans untuk kembali ke URL ini setelah selesai.
                'callbacks' => [
                    'finish' => 'https://myapp.com/finish'
                ]
                // -------------------------
            ];

            // 3. Dapatkan Payment URL dari Midtrans
            $paymentUrl = Snap::createTransaction($midtransParams)->redirect_url;

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tagihan top-up berhasil dibuat. Silakan lanjutkan pembayaran.',
                'data' => [
                    'tagihan' => $tagihan,
                    'payment_url' => $paymentUrl,
                ]
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal membuat tagihan top-up: ' . $e->getMessage()], 500);
        }
    }
}
