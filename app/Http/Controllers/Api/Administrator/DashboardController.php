<?php

namespace App\Http\Controllers\Api\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\Orang;
use App\Models\Pembayaran;
use App\Models\Pengembalian;
use App\Models\Tabung;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Mengambil data statistik utama untuk ditampilkan di dashboard administrator.
     */
    public function getStats(Request $request)
    {
        try {
            $akun = $request->user()->load('orang', 'role');

            // [PERBAIKAN] Statistik disesuaikan dengan kebutuhan baru

            // 1. Peminjaman Berlangsung
            $totalPeminjaman = TransaksiDetail::whereHas('jenisTransaksiDetail', function ($q) {
                $q->where('jenis_transaksi', 'peminjaman');
            })->count();
            $totalPengembalian = Pengembalian::count();
            $peminjamanBerlangsung = $totalPeminjaman - $totalPengembalian;

            // 2. Jumlah Total Tabung
            $jumlahTabung = Tabung::count();

            // 3. Stok Tabung Tersedia (Asumsi id_status_tabung = 1 adalah 'tersedia')
            $stokTersedia = Tabung::where('id_status_tabung', 1)->count();

            // 4. Jumlah Total Transaksi (termasuk yang valid dan batal)
            $totalTransaksi = Transaksi::count();

            // 5. Jumlah Pelanggan Aktif
            $pelangganAktif = Akun::where('status_aktif', true)
                ->whereHas('role', function ($query) {
                    $query->where('nama_role', 'pelanggan');
                })->count();

            $data = [
                'akun' => $akun,
                'statistik' => [
                    'peminjaman_berlangsung' => $peminjamanBerlangsung,
                    'jumlah_tabung' => $jumlahTabung,
                    'stok_tersedia' => $stokTersedia,
                    'total_transaksi' => $totalTransaksi,
                    'pelanggan_aktif' => $pelangganAktif,
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
