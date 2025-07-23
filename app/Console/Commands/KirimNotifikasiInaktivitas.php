<?php

namespace App\Console\Commands;

use App\Models\FcmToken;
use App\Models\Notifikasi;
use App\Models\NotifikasiTemplate;
use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class KirimNotifikasiInaktivitas extends Command
{
    private const HARI_PERINGATAN = 27;
    protected $signature = 'notifikasi:cek-inaktivitas';
    protected $description = 'Mengirim notifikasi peringatan kepada pelanggan jika peminjaman mendekati 30 hari tanpa aktivitas.';

    public function handle()
    {
        $this->info('Memulai pengecekan untuk notifikasi inaktivitas...');

        // 1. Ambil template dari database SATU KALI saja untuk efisiensi
        try {
            $template = NotifikasiTemplate::where('nama_template', 'peringatan_inaktivitas')->firstOrFail();
        } catch (\Exception $e) {
            $this->error('Template notifikasi "peringatan_inaktivitas" tidak ditemukan di database.');
            return;
        }

        $peminjamanAktif = Peminjaman::where('status_pinjam', true)->get();

        foreach ($peminjamanAktif as $peminjaman) {
            $tanggalAktivitas = Carbon::parse($peminjaman->tanggal_aktivitas_terakhir);
            $selisihHari = $tanggalAktivitas->diffInDays(Carbon::now());

            if ($selisihHari >= self::HARI_PERINGATAN && $selisihHari < 30) {

                $notifikasiTerkirim = Notifikasi::where('id_peminjaman', $peminjaman->id_peminjaman)
                    ->where('id_template', $template->id_notifikasi_template)
                    ->where('created_at', '>=', $tanggalAktivitas->copy()->addDays(self::HARI_PERINGATAN))
                    ->exists();

                if (!$notifikasiTerkirim) {
                    $this->line("Mengirim notifikasi untuk peminjaman #{$peminjaman->id_peminjaman}...");
                    $this->kirimNotifikasi($peminjaman, $template);
                }
            }
        }

        $this->info('Pengecekan notifikasi selesai.');
    }

    private function kirimNotifikasi(Peminjaman $peminjaman, NotifikasiTemplate $template)
    {
        $akun = $peminjaman->akun;
        $tokens = FcmToken::where('id_akun', $akun->id_akun)->pluck('token')->toArray();

        if (empty($tokens)) {
            $this->warn("Tidak ada FCM token ditemukan untuk akun #{$akun->id_akun}.");
            return;
        }

        // 2. Personalisasi pesan dengan data dinamis
        $sisaHari = 30 - Carbon::parse($peminjaman->tanggal_aktivitas_terakhir)->diffInDays(Carbon::now());

        $placeholders = [
            '{sisa_hari}' => $sisaHari,
            '{nama_pelanggan}' => $akun->orang->nama_lengkap, // Contoh placeholder lain
        ];

        $judul = str_replace(array_keys($placeholders), array_values($placeholders), $template->judul);
        $isi = str_replace(array_keys($placeholders), array_values($placeholders), $template->isi);

        // 3. Kirim notifikasi menggunakan Firebase
        $serverKey = env('FCM_SERVER_KEY');
        Http::withToken($serverKey)->withHeaders(['Content-Type' => 'application/json'])
            ->post('https://fcm.googleapis.com/v1/projects/' . env('FCM_PROJECT_ID') . '/messages:send', [
                'message' => [
                    'tokens' => $tokens,
                    'notification' => ['title' => $judul, 'body' => $isi],
                    'data' => ['peminjaman_id' => (string)$peminjaman->id_peminjaman, 'screen' => 'detail_peminjaman']
                ]
            ]);

        // 4. Simpan catatan notifikasi di database menggunakan ID template
        Notifikasi::create([
            'id_akun' => $akun->id_akun,
            'id_peminjaman' => $peminjaman->id_peminjaman,
            'id_template' => $template->id_notifikasi_template, // <-- Menggunakan ID template
            'tanggal_terjadwal' => now(),
            'waktu_dikirim' => now(),
        ]);
    }
}
