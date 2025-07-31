<?php

namespace App\Http\Controllers\Api\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Transaksi;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Midtrans\Config;
use Midtrans\Snap;

class TagihanPelangganController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Menampilkan rekapitulasi total sisa tagihan milik pelanggan yang sedang login.
     */
    public function getRekapitulasi(Request $request)
    {
        try {
            $akun = $request->user();
            $orang = $akun->orang;

            $totalUtang = Transaksi::where('id_orang', $orang->id_orang)->where('status_valid', true)->sum('total_transaksi');
            $totalBayar = Pembayaran::where('id_orang', $orang->id_orang)->sum('jumlah_pembayaran');
            $sisaTagihan = $totalUtang - $totalBayar;

            return response()->json([
                'success' => true,
                'message' => 'Rekapitulasi tagihan berhasil diambil.',
                'data'    => ['sisa_tagihan' => (float) $sisaTagihan]
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengambil rekapitulasi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Memulai proses pembayaran untuk sisa tagihan.
     */
    public function bayar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jumlah_pembayaran' => 'required|numeric|min:10000',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'data' => ['errors' => $validator->errors()]], 422);
        }

        DB::beginTransaction();
        try {
            $akun = $request->user();
            $orang = $akun->orang;
            $jumlahBayar = $request->jumlah_pembayaran;

            // Verifikasi di backend: pastikan jumlah bayar tidak melebihi sisa tagihan
            $totalUtang = Transaksi::where('id_orang', $orang->id_orang)->where('status_valid', true)->sum('total_transaksi');
            $totalBayar = Pembayaran::where('id_orang', $orang->id_orang)->sum('jumlah_pembayaran');
            $sisaTagihan = $totalUtang - $totalBayar;

            if ($jumlahBayar > $sisaTagihan) {
                throw new Exception("Jumlah pembayaran melebihi sisa tagihan Anda sebesar " . number_format($sisaTagihan));
            }

            // Buat Order ID unik untuk pelunasan
            $orderId = 'PELUNASAN-' . $orang->id_orang . '-' . time();

            $midtransParams = [
                'transaction_details' => ['order_id' => $orderId, 'gross_amount' => $jumlahBayar],
                'customer_details' => ['first_name' => $akun->orang->nama_lengkap, 'email' => $akun->email, 'phone' => $akun->orang->no_telepon],
                'item_details' => [['id' => 'PELUNASAN01', 'price' => $jumlahBayar, 'quantity' => 1, 'name' => 'Pembayaran Sisa Tagihan']],
                'callbacks' => ['finish' => 'https://myapp.com/finish']
            ];

            $paymentUrl = Snap::createTransaction($midtransParams)->redirect_url;

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Link pembayaran berhasil dibuat.',
                'data'    => ['payment_url' => $paymentUrl]
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()], 500);
        }
    }
}
