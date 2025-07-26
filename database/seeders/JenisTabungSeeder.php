<?php

namespace Database\Seeders;

use App\Models\JenisTabung;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JenisTabungSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        JenisTabung::firstOrCreate(
            ['nama_jenis' => 'Oksigen Kecil O2 m³'],
            ['harga_pinjam' => 10000, 'harga_isi_ulang' => 50000, 'nilai_deposit' => 1000000]
        );
        JenisTabung::firstOrCreate(
            ['nama_jenis' => 'Oksigen Besar O2 6m³'],
            ['harga_pinjam' => 20000, 'harga_isi_ulang' => 100000, 'nilai_deposit' => 1500000]
        );
        JenisTabung::firstOrCreate(
            ['nama_jenis' => 'Nitrogen'],
            ['harga_pinjam' => 15000, 'harga_isi_ulang' => 170000, 'nilai_deposit' => 1500000]
        );
        JenisTabung::firstOrCreate(
            ['nama_jenis' => 'Argon'],
            ['harga_pinjam' => 15000, 'harga_isi_ulang' => 280000, 'nilai_deposit' => 1500000]
        );
        JenisTabung::firstOrCreate(
            ['nama_jenis' => 'Acetelyne'],
            ['harga_pinjam' => 15000, 'harga_isi_ulang' => 350000, 'nilai_deposit' => 1500000]
        );
        JenisTabung::firstOrCreate(
            ['nama_jenis' => 'Dinitrogen Monoksida N2O'],
            ['harga_pinjam' => 15000, 'harga_isi_ulang' => 4900000, 'nilai_deposit' => 5000000]
        );
    }
}
