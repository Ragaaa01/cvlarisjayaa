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
    public function peminjaman(Request $request)
    {
        try {
            $orang = $request->user()->orang;
            $riwayat = TransaksiDetail::whereHas('transaksi', fn($q) => $q->where('id_orang', $orang->id_orang))
                ->where('id_jenis_transaksi_detail', 1) // Hanya peminjaman
                ->with(['transaksi', 'tabung.jenisTabung', 'pengembalian'])
                ->latest()
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
            $riwayat = TransaksiDetail::whereHas('transaksi', fn($q) => $q->where('id_orang', $orang->id_orang))
                ->where('id_jenis_transaksi_detail', 2) // Hanya isi ulang
                ->with(['transaksi', 'tabung.jenisTabung'])
                ->latest()
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
                ], 404); // 404 Not Found adalah status yang tepat untuk ini.
            }

            $peminjaman = Tabung::with('jenisTabung')
                ->whereHas('transaksiDetails', function ($query) use ($orang) {
                    // Cari detail transaksi yang...
                    // 1. Belum memiliki data pengembalian
                    $query->whereDoesntHave('pengembalian')
                        // 2. Dan transaksi induknya milik pelanggan yang sedang login & sudah valid
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
