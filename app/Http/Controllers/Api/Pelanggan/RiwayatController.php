<?php

namespace App\Http\Controllers\Api\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\Pengisian;
use App\Models\Tagihan;
use App\Models\RiwayatDeposit;
use Illuminate\Http\Request;
use Exception;

class RiwayatController extends Controller
{
    /**
     * Menampilkan daftar riwayat peminjaman milik pelanggan yang sedang login.
     */
    public function peminjaman(Request $request)
    {
        try {
            $akun = $request->user();
            $riwayatPeminjaman = Peminjaman::where('id_akun', $akun->id_akun)
                ->with(['detailPeminjamans.tabung.jenisTabung', 'tagihan'])
                ->latest('tanggal_pinjam')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'message' => 'Riwayat peminjaman berhasil diambil.',
                'data' => $riwayatPeminjaman
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat peminjaman: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan daftar riwayat isi ulang milik pelanggan yang sedang login.
     */
    public function isiUlang(Request $request)
    {
        try {
            $akun = $request->user();
            $riwayatIsiUlang = Pengisian::where('id_akun', $akun->id_akun)
                ->with(['detailPengisians.tabung.jenisTabung', 'tagihan'])
                ->latest('waktu_transaksi')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'message' => 'Riwayat isi ulang berhasil diambil.',
                'data' => $riwayatIsiUlang
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat isi ulang: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan daftar riwayat tagihan milik pelanggan yang sedang login.
     */
    public function tagihan(Request $request)
    {
        try {
            $akun = $request->user();
            $riwayatTagihan = Tagihan::where('id_akun', $akun->id_akun)
                ->with('pembayaranTagihans')
                ->latest()
                ->paginate(15);

            return response()->json([
                'success' => true,
                'message' => 'Riwayat tagihan berhasil diambil.',
                'data' => $riwayatTagihan
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat tagihan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan riwayat mutasi deposit milik pelanggan yang sedang login.
     */
    public function deposit(Request $request)
    {
        try {
            $akun = $request->user();
            $deposit = $akun->deposit;

            // Jika pelanggan belum pernah punya deposit
            if (!$deposit) {
                return response()->json([
                    'success' => true,
                    'message' => 'Anda belum memiliki riwayat deposit.',
                    'data' => []
                ], 200);
            }

            $riwayatDeposit = RiwayatDeposit::where('id_deposit', $deposit->id_deposit)
                ->latest('waktu_aktivitas')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'message' => 'Riwayat deposit berhasil diambil.',
                'data' => $riwayatDeposit
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat deposit: ' . $e->getMessage()
            ], 500);
        }
    }
}
