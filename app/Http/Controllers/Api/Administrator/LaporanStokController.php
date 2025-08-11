<?php

namespace App\Http\Controllers\Api\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanStokController extends Controller
{
    public function rekapitulasiBulanan(Request $request)
    {
        // Query untuk menghitung jumlah tabung berdasarkan jenis, status, dan dikelompokkan per bulan
        $laporan = DB::table('tabungs')
            ->join('jenis_tabungs', 'tabungs.id_jenis_tabung', '=', 'jenis_tabungs.id_jenis_tabung')
            ->join('status_tabungs', 'tabungs.id_status_tabung', '=', 'status_tabungs.id_status_tabung')
            ->select(
                // Mengambil tahun dan bulan dari created_at
                DB::raw("DATE_FORMAT(tabungs.created_at, '%Y-%m') as bulan"),
                'jenis_tabungs.nama_jenis',
                'status_tabungs.status_tabung',
                DB::raw('count(tabungs.id_tabung) as jumlah')
            )
            ->groupBy('bulan', 'jenis_tabungs.nama_jenis', 'status_tabungs.status_tabung')
            ->orderBy('bulan', 'desc')
            ->get();

        // Mengolah data agar mudah digunakan di frontend
        $hasil = [];
        foreach ($laporan as $item) {
            $bulan = $item->bulan;
            if (!isset($hasil[$bulan])) {
                $hasil[$bulan] = [];
            }
            if (!isset($hasil[$bulan][$item->nama_jenis])) {
                $hasil[$bulan][$item->nama_jenis] = [
                    'tersedia' => 0,
                    'dipinjam' => 0,
                    'rusak' => 0,
                    'hilang' => 0,
                ];
            }
            $hasil[$bulan][$item->nama_jenis][$item->status_tabung] = $item->jumlah;
        }

        return response()->json([
            'success' => true,
            'message' => 'Laporan rekapitulasi stok berhasil diambil.',
            'data' => $hasil
        ], 200);
    }
}
