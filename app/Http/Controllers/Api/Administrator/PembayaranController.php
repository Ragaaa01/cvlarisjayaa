<?php

namespace App\Http\Controllers\Api\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Orang;
use App\Models\Pembayaran;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;

class PembayaranController extends Controller
{
    /**
     * Mencatat pembayaran tunai yang diterima dari pelanggan untuk melunasi sisa tagihan.
     */
    public function storeTunai(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_orang' => 'required|exists:orangs,id_orang',
            'jumlah_pembayaran' => 'required|numeric|min:1',
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
            $idOrang = $request->id_orang;
            $jumlahBayar = $request->jumlah_pembayaran;

            // 1. Hitung total sisa tagihan pelanggan saat ini
            $totalUtang = Transaksi::where('id_orang', $idOrang)->where('status_valid', true)->sum('total_transaksi');
            $totalBayarSebelumnya = Pembayaran::where('id_orang', $idOrang)->sum('jumlah_pembayaran');
            $sisaTagihanSaatItu = $totalUtang - $totalBayarSebelumnya;

            // 2. Validasi: Pastikan jumlah bayar tidak melebihi sisa tagihan
            if ($jumlahBayar > $sisaTagihanSaatItu) {
                throw new Exception("Jumlah pembayaran (Rp " . number_format($jumlahBayar) . ") melebihi sisa tagihan (Rp " . number_format($sisaTagihanSaatItu) . ").");
            }

            // 3. Buat catatan pembayaran baru
            $pembayaran = Pembayaran::create([
                'id_orang' => $idOrang,
                'total_transaksi' => $sisaTagihanSaatItu, // Catat total utang saat itu
                'jumlah_pembayaran' => $jumlahBayar,
                'metode_pembayaran' => 'tunai', // Metode pembayaran tunai
                'nomor_referensi' => 'TUNAI-' . time(), // Referensi internal
                'tanggal_pembayaran' => now()->toDateString(),
                'waktu_pembayaran' => now()->toTimeString(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran tunai berhasil dicatat.',
                'data'    => $pembayaran
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat pembayaran: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }
}
