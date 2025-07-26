<?php

namespace App\Http\Controllers\Api\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\DetailPengembalian;
use App\Models\Denda;
use App\Models\RiwayatDeposit;
use App\Models\Tagihan;
use App\Models\Tabung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;

class PengembalianController extends Controller
{
    // Definisikan biaya administrasi sebagai konstanta agar mudah diubah
    private const BIAYA_ADMINISTRASI = 50000;

    /**
     * Memproses pengembalian tabung, menghitung denda, dan mengelola deposit.
     */
    public function store(Request $request, $id_peminjaman)
    {
        $validator = Validator::make($request->all(), [
            'detail_kembali' => 'required|array|min:1',
            'detail_kembali.*.id_tabung' => 'required|exists:tabungs,id_tabung',
            'detail_kembali.*.kondisi' => 'required|in:baik,rusak,hilang',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('akun.deposit')->findOrFail($id_peminjaman);
            $akun = $peminjaman->akun;
            $deposit = $akun->deposit;

            if (!$peminjaman->status_pinjam) {
                throw new Exception("Peminjaman ini sudah selesai dan tidak bisa diproses lagi.");
            }

            $tagihanBaru = null;

            // 1. Buat catatan Denda untuk Kerusakan/Kehilangan (jika ada)
            foreach ($request->detail_kembali as $item) {
                if ($item['kondisi'] === 'rusak' || $item['kondisi'] === 'hilang') {
                    $tabung = Tabung::with('jenisTabung')->find($item['id_tabung']);
                    $dendaKerusakan = $tabung->jenisTabung->nilai_deposit; // Denda = nilai jaminan tabung

                    Denda::create([
                        'id_peminjaman' => $peminjaman->id_peminjaman,
                        'id_akun' => $akun->id_akun,
                        'jenis_denda' => $item['kondisi'],
                        'jumlah_denda' => $dendaKerusakan,
                    ]);
                }
            }

            // 2. [PERBAIKAN] Kumpulkan SEMUA denda yang terkait dengan peminjaman ini
            // Ini akan mencakup denda inaktivitas (dari cron job) dan denda kerusakan (dari langkah 1)
            $totalDenda = Denda::where('id_peminjaman', $peminjaman->id_peminjaman)->sum('jumlah_denda');

            // 3. Potong Saldo Deposit
            $saldoAwal = $deposit ? $deposit->saldo : 0;
            $totalPotongan = $totalDenda + self::BIAYA_ADMINISTRASI;

            if ($totalDenda > 0) {
                RiwayatDeposit::create([
                    'id_deposit' => $deposit->id_deposit,
                    'jenis_aktivitas' => 'potong_denda',
                    'jumlah' => $totalDenda,
                    'keterangan' => 'Potongan akumulasi denda untuk peminjaman #' . $peminjaman->id_peminjaman,
                ]);
            }
            RiwayatDeposit::create([
                'id_deposit' => $deposit->id_deposit,
                'jenis_aktivitas' => 'potong_biaya_admin',
                'jumlah' => self::BIAYA_ADMINISTRASI,
                'keterangan' => 'Biaya administrasi pengembalian peminjaman #' . $peminjaman->id_peminjaman,
            ]);

            // Cek apakah deposit cukup
            if ($saldoAwal >= $totalPotongan) {
                $deposit->decrement('saldo', $totalPotongan);
            } else {
                // Jika deposit tidak cukup, habiskan saldo dan buat tagihan baru
                $sisaUtang = $totalPotongan - $saldoAwal;
                if ($deposit) {
                    $deposit->update(['saldo' => 0]);
                }

                $tagihanBaru = Tagihan::create([
                    'id_akun' => $akun->id_akun,
                    'total_tagihan' => $sisaUtang,
                    'sisa' => $sisaUtang,
                    'status_tagihan' => 'belum_lunas',
                ]);
            }

            // 4. Hitung Sisa Deposit yang akan Dikembalikan
            $sisaDepositDikembalikan = $deposit ? $deposit->fresh()->saldo : 0;

            // 5. Buat Catatan Pengembalian
            $pengembalian = Pengembalian::create([
                'id_peminjaman' => $peminjaman->id_peminjaman,
                'tanggal_kembali' => now(),
            ]);

            foreach ($request->detail_kembali as $item) {
                DetailPengembalian::create([
                    'id_pengembalian' => $pengembalian->id_pengembalian,
                    'id_tabung' => $item['id_tabung'],
                    'kondisi_tabung' => $item['kondisi'],
                ]);
                // 6. Update Status Tabung menjadi 'tersedia'
                Tabung::find($item['id_tabung'])->update(['id_status_tabung' => 1]); // Asumsi 1 = 'tersedia'
            }

            // 7. Update Status Peminjaman menjadi 'selesai'
            $peminjaman->update(['status_pinjam' => false]);

            // Jika ada sisa deposit, catat sebagai pengembalian dana dan nolkan saldo
            if ($sisaDepositDikembalikan > 0) {
                RiwayatDeposit::create([
                    'id_deposit' => $deposit->id_deposit,
                    'jenis_aktivitas' => 'pengembalian_dana',
                    'jumlah' => $sisaDepositDikembalikan,
                    'keterangan' => 'Pengembalian sisa deposit tunai.',
                ]);
                $deposit->update(['saldo' => 0]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Proses pengembalian berhasil diselesaikan.',
                'data' => [
                    'saldo_awal_deposit' => $saldoAwal,
                    'total_denda' => $totalDenda,
                    'biaya_administrasi' => self::BIAYA_ADMINISTRASI,
                    'total_potongan' => $totalPotongan,
                    'sisa_deposit_dikembalikan' => $sisaDepositDikembalikan,
                    'tagihan_baru' => $tagihanBaru,
                ]
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memproses pengembalian: ' . $e->getMessage()], 500);
        }
    }
}
