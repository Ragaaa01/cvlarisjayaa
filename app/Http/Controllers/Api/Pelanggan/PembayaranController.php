<?php

namespace App\Http\Controllers\Api\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use App\Models\PembayaranTagihan;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;

class PembayaranController extends Controller
{
    /**
     * Memproses pembayaran untuk sebuah tagihan.
     * Dalam aplikasi nyata, ini bisa dipicu oleh callback dari payment gateway.
     */
    public function proses(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_tagihan' => 'required|exists:tagihans,id_tagihan',
            'metode_pembayaran' => 'required|in:tunai,transfer,gopay,ovo', // Sesuaikan dengan metode Anda
            'jumlah_dibayar' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $akun = $request->user();
            $tagihan = Tagihan::where('id_tagihan', $request->id_tagihan)
                ->where('id_akun', $akun->id_akun)
                ->firstOrFail();

            if ($tagihan->status_tagihan === 'lunas') {
                throw new Exception("Tagihan ini sudah lunas.");
            }

            if ($request->jumlah_dibayar < $tagihan->sisa) {
                throw new Exception("Jumlah pembayaran kurang dari sisa tagihan.");
            }

            // 1. Catat pembayaran
            PembayaranTagihan::create([
                'id_tagihan' => $tagihan->id_tagihan,
                'jumlah_dibayar' => $request->jumlah_dibayar,
                'metode_pembayaran' => $request->metode_pembayaran,
            ]);

            // 2. Update status tagihan
            $tagihan->update([
                'jumlah_dibayar' => $tagihan->jumlah_dibayar + $request->jumlah_dibayar,
                'sisa' => 0,
                'status_tagihan' => 'lunas',
            ]);

            // 3. Update status peminjaman menjadi aktif (jika terkait)
            // Ini akan menandakan pesanan siap untuk disiapkan oleh admin
            Peminjaman::where('id_tagihan', $tagihan->id_tagihan)->update(['status_pinjam' => true]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil. Pesanan Anda akan segera diproses oleh administrator.',
                'data' => $tagihan
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()], 500);
        }
    }
}
