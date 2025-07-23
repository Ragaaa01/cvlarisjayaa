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
                'nama_template' => 'peringatan_inaktivitas',
                'hari_set' => 27, // Dikirim pada hari ke-27 setelah aktivitas terakhir
                'judul' => 'Peringatan Peminjaman',
                'isi' => 'Halo {nama_pelanggan}, peminjaman Anda akan dikenakan denda dalam {sisa_hari} hari. Segera lakukan isi ulang untuk mereset periode denda.'
            ],
            [
                'nama_template' => 'pembayaran_berhasil',
                'hari_set' => null,
                'judul' => 'Pembayaran Berhasil',
                'isi' => 'Terima kasih, pembayaran Anda untuk tagihan #{id_tagihan} sejumlah Rp {jumlah} telah kami terima.'
            ],
            [
                'nama_template' => 'pesanan_siap_diambil',
                'hari_set' => null,
                'judul' => 'Pesanan Siap Diambil',
                'isi' => 'Pesanan peminjaman Anda #{id_peminjaman} sudah kami siapkan dan siap untuk diambil.'
            ],
            [
                'nama_template' => 'akun_diaktivasi',
                'hari_set' => null,
                'judul' => 'Akun Anda Telah Aktif!',
                'isi' => 'Selamat datang, {nama_pelanggan}! Akun Anda telah diaktivasi oleh administrator dan siap digunakan.'
            ],
            [
                'nama_template' => 'tagihan_denda_baru',
                'hari_set' => null,
                'judul' => 'Tagihan Denda Baru',
                'isi' => 'Anda memiliki tagihan denda baru sejumlah Rp {jumlah} karena {alasan_denda}. Silakan cek aplikasi untuk detailnya.'
            ],
        ];

        foreach ($templates as $template) {
            // Menggunakan firstOrCreate untuk mencegah duplikasi jika seeder dijalankan berkali-kali
            NotifikasiTemplate::firstOrCreate(
                ['nama_template' => $template['nama_template']],
                $template
            );
        }
    }
}
