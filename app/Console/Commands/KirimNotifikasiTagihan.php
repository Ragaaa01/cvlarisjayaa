<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Akun;
use App\Models\Transaksi;
use App\Models\Pembayaran;
use App\Models\NotifikasiTemplate;
use App\Models\Notifikasi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Exception;
use Kreait\Firebase\Messaging\AndroidConfig;

class KirimNotifikasiTagihan extends Command
{
    protected $signature = 'notifikasi:kirim-tagihan';
    protected $description = 'Memeriksa dan mengirim notifikasi tagihan kepada pelanggan.';

    public function handle()
    {
        $this->info('Memulai proses pengiriman notifikasi tagihan...');

        $akuns = Akun::where('status_aktif', true)
            ->whereHas('role', function ($q) {
                $q->where('nama_role', 'pelanggan');
            })
            ->with('orang')
            ->get();

        foreach ($akuns as $akun) {
            try {
                $orang = $akun->orang;
                if (!$orang) continue;

                $totalUtang = Transaksi::where('id_orang', $orang->id_orang)->where('status_valid', true)->sum('total_transaksi');
                $totalBayar = Pembayaran::where('id_orang', $orang->id_orang)->sum('jumlah_pembayaran');
                $sisaTagihan = $totalUtang - $totalBayar;

                if ($sisaTagihan > 0) {
                    // [PERBAIKAN UTAMA] Cari transaksi TERTUA yang valid, bukan yang terbaru.
                    $transaksiPertama = Transaksi::where('id_orang', $orang->id_orang)
                        ->where('status_valid', true)
                        ->oldest('tanggal_transaksi') // <-- Menggunakan oldest()
                        ->first();

                    if ($transaksiPertama) {
                        $tanggalTransaksi = Carbon::parse($transaksiPertama->tanggal_transaksi);
                        $hariBerlalu = $tanggalTransaksi->diffInDays(now());

                        $template = null;
                        if ($hariBerlalu == 27) { // H-3
                            $template = NotifikasiTemplate::where('nama_template', 'pengingat_h-3')->first();
                        } elseif ($hariBerlalu == 28) { // H-2
                            $template = NotifikasiTemplate::where('nama_template', 'pengingat_h-2')->first();
                        } elseif ($hariBerlalu == 29) { // H-1
                            $template = NotifikasiTemplate::where('nama_template', 'pengingat_h-1')->first();
                        } elseif ($hariBerlalu >= 30) { // Hari H dan seterusnya
                            $template = NotifikasiTemplate::where('nama_template', 'pengingat_jatuh_tempo')->first();
                        }

                        if ($template) {
                            $this->kirimNotifikasi($akun, $template, $sisaTagihan);
                        }
                    }
                }
            } catch (Exception $e) {
                $this->error("Gagal memproses akun #{$akun->id_akun}: " . $e->getMessage());
            }
        }

        $this->info('Proses pengiriman notifikasi tagihan selesai.');
    }

    /**
     * Fungsi untuk mengirim notifikasi ke satu akun.
     */
    protected function kirimNotifikasi(Akun $akun, NotifikasiTemplate $template, $sisaTagihan)
    {
        // Pengecekan untuk memastikan relasi 'orang' tidak null
        if (!$akun->orang) {
            $this->warn("Akun #{$akun->id_akun} tidak memiliki data 'orang' yang terhubung. Dilewati.");
            return;
        }

        $sudahDikirim = Notifikasi::where('id_akun', $akun->id_akun)
            ->where('id_template', $template->id_notifikasi_template)
            ->whereDate('created_at', today())
            ->exists();

        if ($sudahDikirim) {
            $this->line("Notifikasi untuk akun #{$akun->id_akun} sudah dikirim hari ini. Dilewati.");
            return;
        }

        $tokens = $akun->fcmTokens()->pluck('token')->toArray();
        if (empty($tokens)) {
            $this->warn("Akun #{$akun->id_akun} tidak memiliki FCM token. Dilewati.");
            return;
        }

        // Personalisasi pesan
        $placeholders = ['{nama}', '{jumlah_tagihan}'];
        $values = [
            $akun->orang->nama_lengkap,
            'Rp ' . number_format($sisaTagihan, 0, ',', '.')
        ];

        $judul = str_replace($placeholders, $values, $template->judul);
        $isi = str_replace($placeholders, $values, $template->isi);

        $notifikasiDB = Notifikasi::create([
            'id_akun' => $akun->id_akun,
            'id_template' => $template->id_notifikasi_template,
            'judul' => $judul,
            'isi' => $isi,
            'tanggal_terjadwal' => now(),
            'waktu_dikirim' => now(),
        ]);

        // [PERBAIKAN UTAMA] Membangun pesan menggunakan array sesuai permintaan Anda
        $messagePayload = [
            'notification' => [
                'title' => $judul,
                'body' => $isi,
            ],
            'data' => [
                'judul' => $judul,
                'isi' => $isi,
                'screen' => 'notifikasi_detail',
                'id_notifikasi' => (string)$notifikasiDB->id_notifikasi,
            ],
            'android' => [
                'notification' => [
                    'channel_id' => 'high_importance_channel',
                ],
                'priority' => 'high',
            ],
            'apns' => [
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                    ],
                ],
            ],
        ];

        $message = CloudMessage::fromArray($messagePayload);

        $messaging = app('firebase.messaging');

        $messaging->sendMulticast($message, $tokens);

        $this->info("Notifikasi #{$notifikasiDB->id_notifikasi} berhasil dikirim ke akun #{$akun->id_akun}.");
    }
}
