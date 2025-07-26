<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\PembayaranTagihan;
use App\Models\RiwayatDeposit;
use App\Models\Tagihan;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class MidtransWebhookController extends Controller
{
    /**
     * Menangani notifikasi webhook dari Midtrans secara manual.
     */
    public function handle(Request $request)
    {
        try {
            // 1. Ambil payload JSON langsung dari request Laravel
            $payload = $request->all();

            // 2. Validasi Keamanan (Signature Key) menggunakan data dari payload
            $orderId = $payload['order_id'];
            $statusCode = $payload['status_code'];
            $grossAmount = $payload['gross_amount'];
            $serverKey = config('midtrans.server_key');

            $signatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

            // Verifikasi signature
            if ($payload['signature_key'] !== $signatureKey) {
                return response()->json(['message' => 'Invalid signature.'], 403);
            }

            // 3. Dapatkan ID Tagihan dari Order ID
            $orderParts = explode('-', $orderId);
            if (count($orderParts) < 3) {
                return response()->json(['message' => 'Invalid Order ID format.'], 400);
            }
            $idTagihan = $orderParts[1];

            $tagihan = Tagihan::findOrFail($idTagihan);

            // 4. Proses Logika Bisnis
            if ($tagihan->status_tagihan === 'lunas') {
                return response()->json(['message' => 'Transaction already processed.'], 200);
            }

            $transactionStatus = $payload['transaction_status'];
            $paymentType = $payload['payment_type'];
            $transactionTime = $payload['transaction_time'];
            $transactionId = $payload['transaction_id']; // Ambil transaction_id

            DB::transaction(function () use ($payload, $tagihan, $transactionStatus, $paymentType, $transactionTime, $transactionId) {
                if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {

                    $tagihan->update([
                        'status_tagihan' => 'lunas',
                        'jumlah_dibayar' => $payload['gross_amount'],
                        'sisa' => 0,
                    ]);

                    // --- PERBAIKAN DI SINI ---
                    PembayaranTagihan::create([
                        'id_tagihan' => $tagihan->id_tagihan,
                        'jumlah_dibayar' => $payload['gross_amount'],
                        'metode_pembayaran' => $paymentType,
                        'tanggal_bayar' => $transactionTime,
                        'nomor_referensi' => $transactionId, // <-- Menyimpan ID transaksi dari Midtrans
                    ]);
                    // -------------------------

                    if ($tagihan->jumlah_top_up > 0) {
                        $deposit = $tagihan->akun->deposit;
                        if (!$deposit) {
                            $deposit = Deposit::create(['id_akun' => $tagihan->id_akun]);
                        }
                        $deposit->increment('saldo', $tagihan->jumlah_top_up);

                        RiwayatDeposit::create([
                            'id_deposit' => $deposit->id_deposit,
                            'jenis_aktivitas' => 'top_up',
                            'jumlah' => $tagihan->jumlah_top_up,
                            'keterangan' => 'Top-up dari transaksi #' . $tagihan->id_tagihan,
                            'waktu_aktivitas' => $transactionTime,
                        ]);
                    }

                    Peminjaman::where('id_tagihan', $tagihan->id_tagihan)
                        ->update(['status_pinjam' => true]);
                } else if ($transactionStatus == 'cancel' || $transactionStatus == 'expire' || $transactionStatus == 'deny') {
                    $tagihan->update(['status_tagihan' => 'gagal']);
                }
            });

            return response()->json(['message' => 'Notification processed successfully.'], 200);
        } catch (Exception $e) {
            Log::error('Midtrans Webhook Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return response()->json(['message' => 'Error processing notification: ' . $e->getMessage()], 500);
        }
    }
}
