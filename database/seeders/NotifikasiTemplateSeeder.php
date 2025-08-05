<?php

namespace Database\Seeders;

use App\Models\NotifikasiTemplate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
            [
                'nama_template' => 'pengingat_h-3',
                'hari_set' => null,
                'judul' => 'Pengingat Pembayaran Tagihan',
                'isi' => 'Yth. {nama_lengkap}, tagihan Anda sebesar {jumlah_tagihan} akan jatuh tempo dalam 3 hari. Mohon segera lakukan pembayaran untuk kenyamanan transaksi Anda selanjutnya. Terima kasih.',
            ],
            [
                'nama_template' => 'pengingat_h-2',
                'hari_set' => null,
                'judul' => 'Pengingat Pembayaran Tagihan',
                'isi' => 'Yth. {nama_lengkap}, tagihan Anda sebesar {jumlah_tagihan} akan jatuh tempo dalam 2 hari. Mohon segera lakukan pembayaran untuk kenyamanan transaksi Anda selanjutnya. Terima kasih.',
            ],
            [
                'nama_template' => 'pengingat_h-1',
                'hari_set' => null,
                'judul' => 'Peringatan Terakhir Pembayaran',
                'isi' => 'Yth. {nama_lengkap}, tagihan Anda sebesar {jumlah_tagihan} akan jatuh tempo BESOK. Segera lakukan pembayaran untuk menghindari denda keterlambatan. Terima kasih.',
            ],
            [
                'nama_template' => 'pengingat_jatuh_tempo',
                'hari_set' => null,
                'judul' => 'Tagihan Anda Telah Jatuh Tempo',
                'isi' => 'Yth. {nama_lengkap}, tagihan Anda sebesar {jumlah_tagihan} telah jatuh tempo hari ini. Mohon segera lakukan pembayaran untuk dapat melakukan transaksi kembali.',
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
