<?php

namespace App\Http\Controllers\Api\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Pengembalian;
use App\Models\Tabung;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;

class PengembalianController extends Controller
{
    // Mendefinisikan denda dan biaya sebagai konstanta
    private const DENDA_KETERLAMBATAN_PER_BULAN = 50000;
    private const BIAYA_ADMINISTRASI = 50000;

    /**
     * [BARU] Mengambil daftar semua item peminjaman yang masih aktif untuk seorang pelanggan.
     */
    public function getPeminjamanAktifByOrang($id_orang)
    {
        try {
            $peminjamanAktif = TransaksiDetail::whereHas('transaksi', function ($q) use ($id_orang) {
                $q->where('id_orang', $id_orang);
            })
                ->whereHas('jenisTransaksiDetail', function ($q) {
                    $q->where('jenis_transaksi', 'peminjaman');
                })
                ->whereDoesntHave('pengembalian') // Kunci: Hanya yang belum dikembalikan
                ->with(['tabung.jenisTabung', 'transaksi.orang.mitras', 'tabung.statusTabung', 'jenisTransaksiDetail'])
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data peminjaman aktif berhasil diambil.',
                'data'    => $peminjamanAktif
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    /**
     * Mencari detail transaksi peminjaman yang masih aktif berdasarkan kode tabung.
     */
    public function cariPeminjamanAktifByKodeTabung($kode_tabung)
    {
        try {
            $tabung = Tabung::where('kode_tabung', $kode_tabung)->firstOrFail();

            $detailPeminjaman = TransaksiDetail::where('id_tabung', $tabung->id_tabung)
                ->whereHas('jenisTransaksiDetail', function ($q) {
                    $q->where('jenis_transaksi', 'peminjaman');
                })
                ->whereDoesntHave('pengembalian')
                ->with(['transaksi.orang', 'tabung.jenisTabung', 'tabung.statusTabung'])
                ->latest('created_at')
                ->first();

            if (!$detailPeminjaman) {
                throw new Exception("Tidak ditemukan transaksi peminjaman aktif untuk tabung ini.");
            }

            return response()->json([
                'success' => true,
                'message' => 'Data peminjaman aktif ditemukan.',
                'data'    => $detailPeminjaman
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null
            ], 404);
        }
    }
    /**
     * Memproses pengembalian satu atau lebih tabung dengan logika denda dinamis.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items_kembali' => 'required|array|min:1',
            'items_kembali.*.kode_tabung' => 'required|string|exists:tabungs,kode_tabung',
            'items_kembali.*.kondisi' => 'required|string|in:baik,rusak,hilang',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'data' => ['errors' => $validator->errors()]], 422);
        }

        $hasilPengembalian = [];

        DB::beginTransaction();
        try {
            foreach ($request->items_kembali as $item) {
                $tabung = Tabung::where('kode_tabung', $item['kode_tabung'])->firstOrFail();

                // 1. Cari transaksi peminjaman terakhir yang belum dikembalikan
                $transaksiDetailPeminjaman = TransaksiDetail::where('id_tabung', $tabung->id_tabung)
                    ->whereHas('jenisTransaksiDetail', function ($q) {
                        $q->where('jenis_transaksi', 'peminjaman');
                    })
                    ->whereDoesntHave('pengembalian')
                    ->latest()
                    ->first();

                if (!$transaksiDetailPeminjaman) {
                    throw new Exception("Tidak ditemukan transaksi peminjaman aktif untuk tabung {$item['kode_tabung']}.");
                }

                $transaksiInduk = $transaksiDetailPeminjaman->transaksi;
                $tanggalPinjamAwal = Carbon::parse($transaksiInduk->tanggal_transaksi);

                // 2. Cari aktivitas isi ulang terakhir untuk me-reset timer denda
                $isiUlangTerakhir = TransaksiDetail::where('id_tabung', $tabung->id_tabung)
                    ->whereHas('jenisTransaksiDetail', function ($q) {
                        $q->where('jenis_transaksi', 'isi_ulang');
                    })
                    ->whereHas('transaksi', function ($q) use ($tanggalPinjamAwal) {
                        $q->where('tanggal_transaksi', '>=', $tanggalPinjamAwal);
                    })
                    ->latest('created_at')
                    ->first();

                $tanggalAktivitasTerakhir = $isiUlangTerakhir
                    ? Carbon::parse($isiUlangTerakhir->transaksi->tanggal_transaksi)
                    : $tanggalPinjamAwal;

                // 3. Ambil nilai deposit
                $transaksiDetailDeposit = TransaksiDetail::where('id_transaksi', $transaksiInduk->id_transaksi)
                    ->whereHas('jenisTransaksiDetail', function ($q) {
                        $q->where('jenis_transaksi', 'deposit');
                    })->first();

                $nilaiDeposit = $transaksiDetailDeposit ? $transaksiDetailDeposit->harga : 0;

                // 4. Hitung Denda Keterlambatan (setelah 30 hari)
                $selisihHari = $tanggalAktivitasTerakhir->diffInDays(now());
                $bulanKeterlambatan = 0;
                if ($selisihHari > 30) {
                    $bulanKeterlambatan = floor(($selisihHari - 1) / 30);
                }
                $dendaKeterlambatan = $bulanKeterlambatan * self::DENDA_KETERLAMBATAN_PER_BULAN;

                // 5. Tentukan Kondisi Akhir Tabung
                $kondisiAkhir = $item['kondisi'];
                // --- [LOGIKA BARU] Cek jika denda keterlambatan menghabiskan deposit ---
                if ($nilaiDeposit > 0 && $dendaKeterlambatan >= $nilaiDeposit) {
                    // Jika denda keterlambatan sudah melebihi nilai deposit,
                    // maka tabung secara otomatis dianggap hilang.
                    $kondisiAkhir = 'hilang';
                }
                // --------------------------------------------------------------------

                // 6. Hitung Denda Kondisi Tabung & Tentukan Status Baru
                $dendaKondisi = 0;
                $statusTabungBaruId = 1; // Default 'tersedia'
                if ($kondisiAkhir === 'rusak' || $kondisiAkhir === 'hilang') {
                    $dendaKondisi = $tabung->jenisTabung->nilai_deposit;
                    $statusTabungBaruId = ($kondisiAkhir === 'rusak') ? 3 : 4; // Asumsi 3=rusak, 4=hilang
                }

                // 7. Kalkulasi Keuangan (termasuk biaya admin)
                $totalDenda = $dendaKeterlambatan + $dendaKondisi;
                $totalPotongan = $totalDenda + self::BIAYA_ADMINISTRASI;
                $sisaDeposit = $nilaiDeposit - $totalPotongan;
                $bayarTagihan = $sisaDeposit < 0 ? abs($sisaDeposit) : 0;
                $sisaDepositFinal = max(0, $sisaDeposit);

                // 8. Buat Catatan Pengembalian
                $pengembalian = Pengembalian::create([
                    'id_tabung' => $tabung->id_tabung,
                    'id_transaksi_detail' => $transaksiDetailPeminjaman->id_transaksi_detail,
                    'tanggal_pinjam' => $transaksiInduk->tanggal_transaksi,
                    'waktu_pinjam' => $transaksiInduk->waktu_transaksi,
                    'tanggal_pengembalian' => now()->toDateString(),
                    'waktu_pengembalian' => now()->toTimeString(),
                    'jumlah_keterlambatan_bulan' => $bulanKeterlambatan,
                    'total_denda' => $totalDenda,
                    'deposit' => $nilaiDeposit,
                    'denda_kondisi_tabung' => $dendaKondisi,
                    'id_status_tabung' => $statusTabungBaruId,
                    'sisa_deposit' => $sisaDepositFinal,
                    'bayar_tagihan' => $bayarTagihan,
                ]);

                // 9. Update Status Tabung
                $tabung->update(['id_status_tabung' => $statusTabungBaruId]);

                // 10. Jika ada kekurangan (bayar_tagihan), buat transaksi utang baru
                if ($bayarTagihan > 0) {
                    $transaksiDenda = Transaksi::create([
                        'id_orang' => $transaksiInduk->id_orang,
                        'total_transaksi' => $bayarTagihan,
                        'status_valid' => true,
                        'tanggal_transaksi' => now()->toDateString(),
                        'waktu_transaksi' => now()->toTimeString(),
                    ]);
                    // Asumsi ID jenis transaksi denda = 4
                    TransaksiDetail::create([
                        'id_transaksi' => $transaksiDenda->id_transaksi,
                        'id_tabung' => $tabung->id_tabung,
                        'id_jenis_transaksi_detail' => 4,
                        'harga' => $bayarTagihan,
                    ]);
                }

                $hasilPengembalian[] = $pengembalian;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Proses pengembalian berhasil.',
                'data'    => $hasilPengembalian
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pengembalian: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }
}
