<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class MidtransWebhookController extends Controller
{
    /**
     * Menangani notifikasi webhook dari Midtrans.
     * Disesuaikan untuk desain database baru dengan tabel transaksis dan pembayarans.
     */
    public function handle(Request $request)
    {
        try {
            // 1. Ambil payload JSON langsung dari request Laravel
            $payload = $request->all();

            // 2. Validasi Keamanan (Signature Key)
            $orderId = $payload['order_id'];
            $statusCode = $payload['status_code'];
            $grossAmount = $payload['gross_amount'];
            $serverKey = config('midtrans.server_key');

            $signatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

            if ($payload['signature_key'] !== $signatureKey) {
                return response()->json(['message' => 'Invalid signature.'], 403);
            }

            // 3. Proses Logika Bisnis
            $transactionStatus = $payload['transaction_status'];
            $paymentType = $payload['payment_type'];
            $transactionTime = $payload['transaction_time'];
            $transactionId = $payload['transaction_id'];

            // Bedakan logika berdasarkan awalan Order ID
            $orderParts = explode('-', $orderId);
            $prefix = $orderParts[0];

            DB::transaction(function () use ($payload, $transactionStatus, $paymentType, $transactionTime, $transactionId, $prefix, $orderParts) {
                if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {

                    // Cek apakah pembayaran ini sudah pernah diproses
                    $pembayaranSudahAda = Pembayaran::where('nomor_referensi', $transactionId)->exists();
                    if ($pembayaranSudahAda) {
                        return; // Keluar dari transaksi jika pembayaran sudah ada
                    }

                    if ($prefix === 'TRX') {
                        // --- LOGIKA UNTUK TRANSAKSI BARU ---
                        $idTransaksi = $orderParts[1];
                        $transaksi = Transaksi::findOrFail($idTransaksi);

                        // Update status transaksi menjadi valid/lunas
                        $transaksi->update(['status_valid' => true]);

                        // Catat pembayaran yang terhubung langsung ke transaksi ini
                        Pembayaran::create([
                            'id_orang' => $transaksi->id_orang,
                            'total_transaksi' => $transaksi->total_transaksi, // Total dari transaksi ini
                            'jumlah_pembayaran' => $payload['gross_amount'],
                            'metode_pembayaran' => $paymentType,
                            'nomor_referensi' => $transactionId,
                            'tanggal_pembayaran' => Carbon::parse($transactionTime)->toDateString(),
                            'waktu_pembayaran' => Carbon::parse($transactionTime)->toTimeString(),
                        ]);
                    } elseif ($prefix === 'PELUNASAN') {
                        // --- LOGIKA UNTUK PEMBAYARAN SISA TAGIHAN ---
                        $idOrang = $orderParts[1];

                        // Hitung total utang saat ini SEBELUM pembayaran dicatat
                        $totalUtang = Transaksi::where('id_orang', $idOrang)->where('status_valid', true)->sum('total_transaksi');
                        $totalBayarSebelumnya = Pembayaran::where('id_orang', $idOrang)->sum('jumlah_pembayaran');
                        $sisaTagihanSaatItu = $totalUtang - $totalBayarSebelumnya;

                        // Buat catatan pembayaran baru
                        Pembayaran::create([
                            'id_orang' => $idOrang,
                            'total_transaksi' => $sisaTagihanSaatItu, // Catat total utang saat itu
                            'jumlah_pembayaran' => $payload['gross_amount'],
                            'metode_pembayaran' => $paymentType,
                            'nomor_referensi' => $transactionId,
                            'tanggal_pembayaran' => Carbon::parse($transactionTime)->toDateString(),
                            'waktu_pembayaran' => Carbon::parse($transactionTime)->toTimeString(),
                        ]);
                    }
                }
                // Anda bisa menambahkan logika untuk status 'cancel', 'expire', dll. di sini
            });

            return response()->json(['message' => 'Notification processed successfully.'], 200);
        } catch (Exception $e) {
            Log::error('Midtrans Webhook Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return response()->json(['message' => 'Error processing notification: ' . $e->getMessage()], 500);
        }
    }
}
