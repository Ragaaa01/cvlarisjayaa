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

class KirimNotifikasiTagihan extends Command
{
    protected $signature = 'notifikasi:kirim-tagihan';
    protected $description = 'Memeriksa dan mengirim notifikasi tagihan kepada pelanggan.';

    public function handle()
    {
        $this->info('Memulai proses pengiriman notifikasi tagihan...');

        // 1. Ambil semua akun pelanggan yang aktif
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

                // 2. Hitung sisa tagihan untuk setiap pelanggan
                $totalUtang = Transaksi::where('id_orang', $orang->id_orang)->where('status_valid', true)->sum('total_transaksi');
                $totalBayar = Pembayaran::where('id_orang', $orang->id_orang)->sum('jumlah_pembayaran');
                $sisaTagihan = $totalUtang - $totalBayar;

                if ($sisaTagihan > 0) {
                    // 3. Cari tanggal transaksi terakhir yang belum lunas sepenuhnya
                    $transaksiTerakhir = Transaksi::where('id_orang', $orang->id_orang)
                        ->where('status_valid', true)
                        ->latest('tanggal_transaksi')
                        ->first();

                    if ($transaksiTerakhir) {
                        $tanggalTransaksi = Carbon::parse($transaksiTerakhir->tanggal_transaksi);
                        $hariBerlalu = $tanggalTransaksi->diffInDays(now());

                        // 4. Tentukan template notifikasi berdasarkan hari
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
        // Cek apakah notifikasi dengan template yang sama sudah dikirim hari ini
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
        $judul = str_replace('{nama}', $akun->orang->nama_lengkap, $template->judul);
        $isi = str_replace('{jumlah_tagihan}', 'Rp ' . number_format($sisaTagihan, 0, ',', '.'), $template->isi);

        // Kirim notifikasi menggunakan Firebase
        $messaging = app('firebase.messaging');
        $notification = FirebaseNotification::create($judul, $isi);
        $message = CloudMessage::new()->withNotification($notification);

        $messaging->sendMulticast($message, $tokens);

        // Catat di database
        Notifikasi::create([
            'id_akun' => $akun->id_akun,
            'id_template' => $template->id_notifikasi_template,
            'tanggal_terjadwal' => now(),
            'waktu_dikirim' => now(),
        ]);

        $this->info("Notifikasi berhasil dikirim ke akun #{$akun->id_akun}.");
    }
}
