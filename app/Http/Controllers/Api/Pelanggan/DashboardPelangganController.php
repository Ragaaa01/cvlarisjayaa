<?php

namespace App\Http\Controllers\Api\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Exception;

class DashboardPelangganController extends Controller
{
    /**
     * Mengambil data ringkas untuk dashboard pelanggan yang sedang login.
     */
    public function index(Request $request)
    {
        try {
            // Mengambil data akun dan orang yang sedang login
            $akun = $request->user()->load('orang');
            $orang = $akun->orang;

            // Menghitung rekapitulasi keuangan
            $totalUtang = Transaksi::where('id_orang', $orang->id_orang)->where('status_valid', true)->sum('total_transaksi');
            $totalBayar = Pembayaran::where('id_orang', $orang->id_orang)->sum('jumlah_pembayaran');
            $sisaTagihan = $totalUtang - $totalBayar;

            // Menyiapkan data untuk dikirim sebagai respons
            $data = [
                'akun' => $akun,
                'rekapitulasi' => [
                    'sisa_tagihan' => (float) $sisaTagihan,
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Data dashboard berhasil diambil.',
                'data'    => $data
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dashboard: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }
}
