<?php

namespace App\Http\Controllers\Api\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\TransaksiDetail;
use Exception;
use Illuminate\Http\Request;

class RiwayatController extends Controller
{
    // public function peminjaman(Request $request)
    // {
    //     try {
    //         $orang = $request->user()->orang;

    //         $riwayat = TransaksiDetail::whereHas('transaksi', function ($q) use ($orang) {
    //             $q->where('id_orang', $orang->id_orang);
    //         })
    //             ->whereHas('jenisTransaksiDetail', function ($q) {
    //                 $q->where('jenis_transaksi', 'peminjaman');
    //             })
    //             ->with(['pengembalian', 'transaksi', 'tabung.jenisTabung']) // Muat relasi pengembalian
    //             ->latest()
    //             ->paginate(10);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Riwayat peminjaman berhasil diambil.',
    //             'data'    => $riwayat
    //         ], 200);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Gagal mengambil riwayat: ' . $e->getMessage(),
    //             'data'    => null
    //         ], 500);
    //     }
    // }
}
