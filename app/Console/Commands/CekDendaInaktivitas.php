<?php

namespace App\Console\Commands;

use App\Models\Denda;
use App\Models\Peminjaman;
use App\Models\RiwayatDeposit;
use App\Models\Tagihan;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CekDendaInaktivitas extends Command
{
    /**
     * Nominal denda per siklus 30 hari.
     *
     * @var int
     */
    private const DENDA_PER_SIKLUS = 50000;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'denda:cek-inaktivitas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengecek peminjaman aktif dan menerapkan denda inaktivitas setiap 30 hari';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pengecekan denda inaktivitas...');

        // Ambil semua peminjaman yang masih aktif
        $peminjamanAktif = Peminjaman::where('status_pinjam', true)->with('akun.deposit')->get();

        foreach ($peminjamanAktif as $peminjaman) {
            DB::beginTransaction();
            try {
                $tanggalAktivitasTerakhir = Carbon::parse($peminjaman->tanggal_aktivitas_terakhir);
                $selisihHari = $tanggalAktivitasTerakhir->diffInDays(Carbon::now());

                // Hitung berapa siklus 30 hari yang sudah terlewat
                $siklusTerlewat = floor($selisihHari / 30);

                // Hitung berapa denda inaktivitas yang sudah tercatat untuk peminjaman ini
                $dendaTercatat = Denda::where('id_peminjaman', $peminjaman->id_peminjaman)
                    ->where('jenis_denda', 'inaktivitas')
                    ->count();

                // Jika ada siklus yang terlewat dan belum didenda, proses denda
                if ($siklusTerlewat > $dendaTercatat) {
                    $jumlahSiklusBaru = $siklusTerlewat - $dendaTercatat;
                    $totalDendaBaru = $jumlahSiklusBaru * self::DENDA_PER_SIKLUS;

                    $this->warn("Peminjaman #{$peminjaman->id_peminjaman} terdeteksi inaktif. Denda baru: Rp " . number_format($totalDendaBaru));

                    $akun = $peminjaman->akun;
                    $deposit = $akun->deposit;

                    // Buat catatan denda untuk setiap siklus baru
                    for ($i = 0; $i < $jumlahSiklusBaru; $i++) {
                        Denda::create([
                            'id_peminjaman' => $peminjaman->id_peminjaman,
                            'id_akun' => $akun->id_akun,
                            'jenis_denda' => 'inaktivitas',
                            'jumlah_denda' => self::DENDA_PER_SIKLUS,
                        ]);
                    }

                    // Potong dari deposit atau buat tagihan
                    if ($deposit && $deposit->saldo >= $totalDendaBaru) {
                        // Jika deposit mencukupi
                        $deposit->decrement('saldo', $totalDendaBaru);
                        RiwayatDeposit::create([
                            'id_deposit' => $deposit->id_deposit,
                            'jenis_aktivitas' => 'potong_denda',
                            'jumlah' => $totalDendaBaru,
                            'keterangan' => "Denda inaktivitas {$jumlahSiklusBaru} siklus untuk peminjaman #{$peminjaman->id_peminjaman}",
                        ]);
                    } else {
                        // Jika deposit tidak cukup atau tidak ada
                        $saldoTersedia = $deposit ? $deposit->saldo : 0;
                        $sisaUtang = $totalDendaBaru - $saldoTersedia;

                        if ($saldoTersedia > 0) {
                            $deposit->update(['saldo' => 0]);
                            RiwayatDeposit::create([
                                'id_deposit' => $deposit->id_deposit,
                                'jenis_aktivitas' => 'potong_denda',
                                'jumlah' => $saldoTersedia,
                                'keterangan' => "Penghabisan saldo deposit untuk denda inaktivitas peminjaman #{$peminjaman->id_peminjaman}",
                            ]);
                        }

                        // Buat atau update tagihan yang ada
                        $tagihan = Tagihan::firstOrCreate(
                            ['id_akun' => $akun->id_akun, 'status_tagihan' => 'belum_lunas'],
                            ['total_tagihan' => 0, 'sisa' => 0]
                        );
                        $tagihan->increment('total_tagihan', $sisaUtang);
                        $tagihan->increment('sisa', $sisaUtang);
                    }
                }
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                $this->error("Gagal memproses peminjaman #{$peminjaman->id_peminjaman}: " . $e->getMessage());
            }
        }

        $this->info('Pengecekan denda inaktivitas selesai.');
    }
}
