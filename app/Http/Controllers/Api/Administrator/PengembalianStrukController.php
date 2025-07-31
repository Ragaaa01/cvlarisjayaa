<?php

namespace App\Http\Controllers\Api\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Pengembalian;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;

class PengembalianStrukController extends Controller
{
    /**
     * Menghasilkan PDF struk untuk satu atau lebih item pengembalian.
     */
    public function downloadStrukPdf(Request $request)
    {
        // Validasi input: pastikan 'ids' adalah array yang berisi angka
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:pengembalians,id_pengembalian',
        ]);

        try {
            $pengembalianIds = $request->input('ids');

            // Ambil semua data pengembalian berdasarkan ID yang dikirim
            $pengembalians = Pengembalian::with([
                'tabung.jenisTabung',
                'statusTabung',
                'transaksiDetail.transaksi.orang.kelurahan.kecamatan.kabupaten.provinsi'
            ])->whereIn('id_pengembalian', $pengembalianIds)->get();

            if ($pengembalians->isEmpty()) {
                throw new Exception('Data pengembalian tidak ditemukan.');
            }

            // Ambil data pelanggan dari item pertama (karena semuanya sama)
            $pelanggan = $pengembalians->first()->transaksiDetail->transaksi->orang;
            $namaFile = 'Struk_Pengembalian_' . str_replace(' ', '_', $pelanggan->nama_lengkap) . '.pdf';

            // Kalkulasi total untuk ringkasan
            $total = [
                'deposit' => $pengembalians->sum('deposit'),
                'denda_keterlambatan' => $pengembalians->sum(function ($item) {
                    return $item->total_denda - $item->denda_kondisi_tabung;
                }),
                'denda_kondisi' => $pengembalians->sum('denda_kondisi_tabung'),
                'sisa_deposit' => $pengembalians->sum('sisa_deposit'),
                'bayar_tagihan' => $pengembalians->sum('bayar_tagihan'),
            ];

            $data = [
                'pengembalians' => $pengembalians,
                'pelanggan' => $pelanggan,
                'total' => $total,
            ];

            $pdf = Pdf::loadView('pdfs.struk_pengembalian', $data);
            return $pdf->download($namaFile);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat PDF struk: ' . $e->getMessage(),
            ], 500);
        }
    }
}
