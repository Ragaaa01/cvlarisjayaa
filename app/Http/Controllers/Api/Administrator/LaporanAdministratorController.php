<?php

namespace App\Http\Controllers\Api\Administrator;

use App\Http\Controllers\Controller;
use App\Models\TransaksiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;
// Jika Anda menggunakan library PDF, tambahkan di sini. Contoh: use Barryvdh\DomPDF\Facade\Pdf;

class LaporanAdministratorController extends Controller
{
    /**
     * Mengambil data laporan peminjaman dan isi ulang untuk bulan dan tahun tertentu.
     */
    public function getLaporanBulanan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|date_format:Y',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Input tidak valid.', 'data' => $validator->errors()], 422);
        }

        try {
            $bulan = $request->query('bulan');
            $tahun = $request->query('tahun');

            $laporan = TransaksiDetail::whereIn('id_jenis_transaksi_detail', [1, 2]) // 1=Peminjaman, 2=Isi Ulang
                ->whereHas('transaksi', function ($query) use ($bulan, $tahun) {
                    $query->where('status_valid', true)
                        ->whereMonth('tanggal_transaksi', $bulan)
                        ->whereYear('tanggal_transaksi', $tahun);
                })
                ->with([
                    'transaksi:id_transaksi,id_orang,tanggal_transaksi,waktu_transaksi',
                    'transaksi.orang:id_orang,nama_lengkap,nik,no_telepon',
                    'tabung:id_tabung,kode_tabung,id_jenis_tabung,id_status_tabung',
                    'tabung.jenisTabung:id_jenis_tabung,nama_jenis',
                    'tabung.statusTabung:id_status_tabung,status_tabung',
                    'jenisTransaksiDetail:id_jenis_transaksi_detail,jenis_transaksi'
                ])
                ->latest('created_at')
                ->paginate(25);

            return response()->json([
                'success' => true,
                'message' => 'Data laporan berhasil diambil.',
                'data'    => $laporan
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data laporan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mengunduh laporan bulanan dalam format PDF.
     * Catatan: Anda perlu menginstal library seperti 'barryvdh/laravel-dompdf'
     */
    public function downloadLaporanBulanan(Request $request)
    {
        // ... Logika validasi dan query yang sama seperti getLaporanBulanan ...
        // ... Setelah mendapatkan data, teruskan ke view Blade dan generate PDF ...

        // Contoh placeholder (implementasi penuh memerlukan library PDF)
        return response()->json(['success' => false, 'message' => 'Fitur unduh PDF belum diimplementasikan.']);
    }
}
