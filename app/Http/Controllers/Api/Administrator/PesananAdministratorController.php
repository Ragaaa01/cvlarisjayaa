<?php

namespace App\Http\Controllers\Api\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Tabung;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PesananAdministratorController extends Controller
{
    /**
     * Mengambil daftar transaksi yang sudah lunas (valid) dan menunggu penyiapan.
     */
    public function getMenungguPenyiapan(Request $request)
    {
        try {
            $query = Transaksi::where('status_valid', true)
                ->whereHas('transaksiDetails', function ($q) {
                    $q->whereIn('id_jenis_transaksi_detail', [1, 2]) // Peminjaman atau Isi Ulang
                        ->whereNull('id_tabung'); // Yang belum ditetapkan tabungnya
                })
                ->with(['orang.mitras']); // Muat data orang dan relasi mitranya

            if ($request->has('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('id_transaksi', 'like', '%' . $request->search . '%')
                        ->orWhereHas('orang', function ($subQ) use ($request) {
                            $subQ->where('nama_lengkap', 'like', '%' . $request->search . '%');
                        });
                });
            }

            $pesanan = $query->latest()->paginate(15);

            return response()->json([
                'success' => true,
                'message' => 'Data pesanan menunggu penyiapan berhasil diambil.',
                'data'    => $pesanan
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * [BARU] Menampilkan detail lengkap dari satu pesanan.
     */
    public function show($id_transaksi)
    {
        try {
            $transaksi = Transaksi::with([
                'orang.mitras',
                'transaksiDetails.jenisTransaksiDetail',
                'transaksiDetails.tabung.jenisTabung' // Untuk melihat tabung yg sudah disiapkan
            ])->findOrFail($id_transaksi);

            return response()->json([
                'success' => true,
                'message' => 'Detail pesanan berhasil diambil.',
                'data'    => $transaksi
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengambil detail pesanan.'], 404);
        }
    }

    /**
     * [DISEMPURNAKAN] Menetapkan tabung fisik ke item pesanan.
     * Untuk peminjaman, fungsi ini akan menetapkan satu tabung ke tiga detail sekaligus.
     */
    public function siapkanPesanan(Request $request, $id_transaksi)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.id_transaksi_detail' => 'required|exists:transaksi_details,id_transaksi_detail',
            'items.*.kode_tabung' => 'required|exists:tabungs,kode_tabung',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'data' => ['errors' => $validator->errors()]], 422);
        }

        DB::beginTransaction();
        try {
            $transaksi = Transaksi::findOrFail($id_transaksi);

            foreach ($request->items as $item) {
                $detail = TransaksiDetail::with('jenisTransaksiDetail')->find($item['id_transaksi_detail']);
                $tabung = Tabung::where('kode_tabung', $item['kode_tabung'])->firstOrFail();

                if ($tabung->id_status_tabung != 1) { // 1 = 'tersedia'
                    throw new Exception("Tabung {$item['kode_tabung']} tidak tersedia.");
                }

                $jenisTransaksi = $detail->jenisTransaksiDetail->jenis_transaksi;

                if ($jenisTransaksi === 'peminjaman') {
                    // --- LOGIKA UTAMA: Tetapkan 1 tabung ke 3 detail ---
                    $transaksi->transaksiDetails()
                        ->whereIn('id_jenis_transaksi_detail', [1, 2, 3]) // Peminjaman, Isi Ulang, Deposit
                        ->whereNull('id_tabung') // Cari yang masih kosong
                        ->take(3) // Ambil 3 baris pertama yang cocok
                        ->update(['id_tabung' => $tabung->id_tabung]);

                    $tabung->update(['id_status_tabung' => 2]); // 2 = 'dipinjam'

                } elseif ($jenisTransaksi === 'isi_ulang') {
                    // --- LOGIKA UNTUK ISI ULANG MANDIRI ---
                    $detail->update(['id_tabung' => $tabung->id_tabung]);
                    // Status tabung tidak diubah karena ini hanya jasa
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Semua tabung berhasil ditetapkan ke pesanan #{$id_transaksi}.",
                'data'    => $transaksi->load('transaksiDetails.tabung')
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyiapkan pesanan: ' . $e->getMessage()], 500);
        }
    }
}
