<?php

namespace Database\Seeders;

use App\Models\NotifikasiTemplate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotifikasiTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'nama_template' => 'peringatan_denda_sewa',
                'hari_set' => 27,
                'judul' => 'Peringatan Peminjaman',
                'isi' => 'Halo {nama_pelanggan}, peminjaman Anda akan dikenakan denda sewa dalam {sisa_hari} hari. Segera lakukan isi ulang untuk mereset periode denda.'
            ],
            [
                'nama_template' => 'pembayaran_berhasil',
                'hari_set' => null,
                'judul' => 'Pembayaran Berhasil',
                'isi' => 'Terima kasih, pembayaran Anda untuk transaksi #{id_transaksi} sejumlah Rp {jumlah} telah kami terima.'
            ],
            [
                'nama_template' => 'akun_diaktivasi',
                'hari_set' => null,
                'judul' => 'Akun Anda Telah Aktif!',
                'isi' => 'Selamat datang, {nama_pelanggan}! Akun Anda telah diaktivasi oleh administrator dan siap digunakan.'
            ],
        ];

        foreach ($templates as $template) {
            NotifikasiTemplate::firstOrCreate(
                ['nama_template' => $template['nama_template']],
                $template
            );
        }
    }
}
