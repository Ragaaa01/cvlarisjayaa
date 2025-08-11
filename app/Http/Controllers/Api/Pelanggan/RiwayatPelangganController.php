<?php

namespace App\Http\Controllers\Api\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Tabung;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Exception;
use Illuminate\Http\Request;

class RiwayatPelangganController extends Controller
{
    /**
     * Mengambil riwayat peminjaman (aktif & selesai).
     */
    // public function peminjaman(Request $request)
    // {
    //     try {
    //         $orang = $request->user()->orang;
    //         $riwayat = TransaksiDetail::where('id_jenis_transaksi_detail', 1)
    //             ->whereHas('transaksi', function ($q) use ($orang) {
    //                 $q->where('id_orang', $orang->id_orang)
    //                     ->where('status_valid', true);
    //             })
    //             ->whereHas('tabung')
    //             ->with(['transaksi', 'tabung.jenisTabung', 'pengembalian'])
    //             ->latest('id_transaksi_detail')
    //             ->paginate(15);

    //         return response()->json(['success' => true, 'data' => $riwayat]);
    //     } catch (Exception $e) {
    //         return response()->json(['success' => false, 'message' => 'Gagal mengambil riwayat peminjaman.'], 500);
    //     }
    // }

    /**
     * Mengambil riwayat peminjaman.
     */
    public function peminjaman(Request $request)
    {
        try {
            $orang = $request->user()->orang;
            $riwayat = TransaksiDetail::where('id_jenis_transaksi_detail', 1) // Hanya peminjaman
                ->whereHas('transaksi', function ($q) use ($orang) {
                    $q->where('id_orang', $orang->id_orang)
                        ->where('status_valid', true); // Tetap ambil transaksi yang sudah valid
                })
                ->whereHas('tabung')
                // [PERBAIKAN] Pastikan relasi statusTabung dari tabung ikut dimuat
                ->with(['transaksi', 'tabung.jenisTabung', 'tabung.statusTabung'])
                ->latest('id_transaksi_detail')
                ->paginate(15);

            return response()->json(['success' => true, 'data' => $riwayat]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengambil riwayat peminjaman.'], 500);
        }
    }


    /**
     * Mengambil riwayat isi ulang.
     */
    public function isiUlang(Request $request)
    {
        try {
            $orang = $request->user()->orang;

            $riwayat = TransaksiDetail::where('id_jenis_transaksi_detail', 2)
                ->whereHas('transaksi', function ($q) use ($orang) {
                    $q->where('id_orang', $orang->id_orang)
                        ->where('status_valid', true);
                })
                ->with(['transaksi', 'tabung.jenisTabung'])
                ->latest('id_transaksi_detail')
                ->paginate(15);

            return response()->json(['success' => true, 'data' => $riwayat]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengambil riwayat isi ulang.'], 500);
        }
    }

    /**
     * Mengambil riwayat pembayaran.
     */
    public function pembayaran(Request $request)
    {
        try {
            $orang = $request->user()->orang;
            $riwayat = Pembayaran::where('id_orang', $orang->id_orang)
                ->latest()
                ->paginate(15);

            return response()->json(['success' => true, 'data' => $riwayat]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengambil riwayat pembayaran.'], 500);
        }
    }

    /**
     * Mengambil daftar tabung yang sedang dipinjam oleh pelanggan.
     */
    public function peminjamanAktif(Request $request)
    {
        try {
            $orang = $request->user()->orang;

            if (!$orang) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil pelanggan tidak lengkap. Silakan perbarui profil Anda.',
                    'data'    => null
                ], 404);
            }

            $peminjaman = Tabung::with('jenisTabung')
                ->whereHas('transaksiDetails', function ($query) use ($orang) {

                    $query->whereDoesntHave('pengembalian')

                        ->whereHas('transaksi', function ($subQuery) use ($orang) {
                            $subQuery->where('id_orang', $orang->id_orang)
                                ->where('status_valid', true);
                        });
                })
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data peminjaman aktif berhasil diambil.',
                'data'    => $peminjaman
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }
}
