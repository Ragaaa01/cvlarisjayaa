<?php

namespace App\Console\Commands;

use App\Models\JenisTransaksiDetail;
use App\Models\Pengembalian;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CekTabungHilangOtomatis extends Command
{
    /**
     * Nominal denda per siklus 30 hari.
     */
    private const DENDA_PER_BULAN = 50000;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transaksi:cek-hilang-otomatis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengecek peminjaman aktif dan menandai tabung sebagai hilang jika denda melebihi deposit';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pengecekan tabung hilang otomatis...');

        $jenisDenda = JenisTransaksiDetail::where('jenis_transaksi', 'denda')->first();
        if (!$jenisDenda) {
            $this->error("Jenis transaksi 'denda' tidak ditemukan di tabel jenis_transaksi_details. Proses dibatalkan.");
            return;
        }
        $idJenisDenda = $jenisDenda->id_jenis_transaksi_detail;

        // 1. Ambil semua item peminjaman yang belum dikembalikan
        $peminjamanAktif = TransaksiDetail::whereHas('jenisTransaksiDetail', function ($q) {
            $q->where('jenis_transaksi', 'peminjaman');
        })
            ->whereDoesntHave('pengembalian')
            ->with(['transaksi', 'tabung.jenisTabung'])
            ->get();

        foreach ($peminjamanAktif as $detailPeminjaman) {
            DB::beginTransaction();
            try {
                $transaksiInduk = $detailPeminjaman->transaksi;
                $tabung = $detailPeminjaman->tabung;
                $tanggalPinjamAwal = Carbon::parse($transaksiInduk->tanggal_transaksi);

                // 2. Cari aktivitas isi ulang terakhir untuk me-reset timer denda
                $isiUlangTerakhir = TransaksiDetail::where('id_tabung', $tabung->id_tabung)
                    ->whereHas('jenisTransaksiDetail', function ($q) {
                        $q->where('jenis_transaksi', 'isi_ulang');
                    })
                    ->whereHas('transaksi', function ($q) use ($tanggalPinjamAwal) {
                        $q->where('tanggal_transaksi', '>=', $tanggalPinjamAwal);
                    })
                    ->latest('created_at')->first();

                $tanggalAktivitasTerakhir = $isiUlangTerakhir
                    ? Carbon::parse($isiUlangTerakhir->transaksi->tanggal_transaksi)
                    : $tanggalPinjamAwal;

                // 3. Hitung denda keterlambatan yang seharusnya
                $selisihHari = $tanggalAktivitasTerakhir->diffInDays(now());
                $bulanKeterlambatan = 0;
                if ($selisihHari > 30) {
                    $bulanKeterlambatan = floor(($selisihHari - 1) / 30);
                }
                $dendaKeterlambatan = $bulanKeterlambatan * self::DENDA_PER_BULAN;

                // 4. Ambil nilai deposit dari transaksi yang sama
                $detailDeposit = TransaksiDetail::where('id_transaksi', $transaksiInduk->id_transaksi)
                    ->whereHas('jenisTransaksiDetail', function ($q) {
                        $q->where('jenis_transaksi', 'deposit');
                    })->first();
                $nilaiDeposit = $detailDeposit ? $detailDeposit->harga : 0;

                // 5. Kondisi Utama: Cek jika denda sudah melebihi deposit
                if ($nilaiDeposit > 0 && $dendaKeterlambatan >= $nilaiDeposit) {
                    $this->warn("Tabung #{$tabung->kode_tabung} dari transaksi #{$transaksiInduk->id_transaksi} dianggap hilang otomatis.");

                    // 6. Buat catatan pengembalian seolah-olah tabung hilang
                    Pengembalian::create([
                        'id_tabung' => $tabung->id_tabung,
                        'id_transaksi_detail' => $detailPeminjaman->id_transaksi_detail,
                        'tanggal_pinjam' => $transaksiInduk->tanggal_transaksi,
                        'waktu_pinjam' => $transaksiInduk->waktu_transaksi,
                        'tanggal_pengembalian' => now()->toDateString(),
                        'waktu_pengembalian' => now()->toTimeString(),
                        'jumlah_keterlambatan_bulan' => $bulanKeterlambatan,
                        'total_denda' => $nilaiDeposit, // Denda maksimal adalah nilai deposit
                        'deposit' => $nilaiDeposit,
                        'denda_kondisi_tabung' => $nilaiDeposit,
                        'id_status_tabung' => 4, // Asumsi 4 = 'hilang'
                        'sisa_deposit' => 0,
                        'bayar_tagihan' => $dendaKeterlambatan - $nilaiDeposit, // Sisa utang jika ada
                    ]);

                    // 7. Update status tabung menjadi 'hilang'
                    $tabung->update(['id_status_tabung' => 4]);

                    // 8. Buat transaksi utang baru jika denda melebihi deposit
                    $sisaUtang = $dendaKeterlambatan - $nilaiDeposit;
                    if ($sisaUtang > 0) {
                        $transaksiDenda = Transaksi::create([
                            'id_orang' => $transaksiInduk->id_orang,
                            'total_transaksi' => $sisaUtang,
                            'status_valid' => true,
                            'tanggal_transaksi' => now()->toDateString(),
                            'waktu_transaksi' => now()->toTimeString(),
                        ]);
                        // Asumsi ID jenis transaksi denda = 4
                        TransaksiDetail::create([
                            'id_transaksi' => $transaksiDenda->id_transaksi,
                            'id_tabung' => $tabung->id_tabung,
                            'id_jenis_transaksi_detail' => $idJenisDenda,
                            'harga' => $sisaUtang,
                        ]);
                    }
                }
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                $this->error("Gagal memproses transaksi #{$detailPeminjaman->id_transaksi}: " . $e->getMessage());
            }
        }

        $this->info('Pengecekan tabung hilang otomatis selesai.');
    }
}
