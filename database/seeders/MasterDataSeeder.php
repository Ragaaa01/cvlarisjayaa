<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data untuk status_tabungs
        DB::table('status_tabungs')->insert([
            ['id_status_tabung' => 1, 'status_tabung' => 'tersedia'],
            ['id_status_tabung' => 2, 'status_tabung' => 'dipinjam'],
            ['id_status_tabung' => 3, 'status_tabung' => 'rusak'],
            ['id_status_tabung' => 4, 'status_tabung' => 'hilang'],
        ]);

        // Data untuk jenis_tabungs
        DB::table('jenis_tabungs')->insert([
            ['id_jenis_tabung' => 1, 'nama_jenis' => 'Oksigen Kecil (O2 1m3)', 'harga_pinjam' => 10000, 'harga_isi_ulang' => 50000, 'nilai_deposit' => 1000000],
            ['id_jenis_tabung' => 2, 'nama_jenis' => 'Oksigen Besar (O2 6m3)', 'harga_pinjam' => 20000, 'harga_isi_ulang' => 100000, 'nilai_deposit' => 1500000],
            ['id_jenis_tabung' => 3, 'nama_jenis' => 'Nitrogen', 'harga_pinjam' => 20000, 'harga_isi_ulang' => 170000, 'nilai_deposit' => 1500000],
            ['id_jenis_tabung' => 4, 'nama_jenis' => 'Argon', 'harga_pinjam' => 30000, 'harga_isi_ulang' => 280000, 'nilai_deposit' => 1500000],
            ['id_jenis_tabung' => 5, 'nama_jenis' => 'Acetelyn', 'harga_pinjam' => 30000, 'harga_isi_ulang' => 350000, 'nilai_deposit' => 1500000],
            ['id_jenis_tabung' => 6, 'nama_jenis' => 'Dinitrogen Monoksida (N2O)', 'harga_pinjam' => 30000, 'harga_isi_ulang' => 4900000, 'nilai_deposit' => 1500000],
        ]);

        // Data awal untuk tabungs
        DB::table('tabungs')->insert([
            ['id_tabung' => 1, 'kode_tabung' => 'OK001', 'id_jenis_tabung' => 1, 'id_status_tabung' => 1],
            ['id_tabung' => 2, 'kode_tabung' => 'OK002', 'id_jenis_tabung' => 1, 'id_status_tabung' => 1],
            ['id_tabung' => 3, 'kode_tabung' => 'OB001', 'id_jenis_tabung' => 2, 'id_status_tabung' => 1],
            ['id_tabung' => 4, 'kode_tabung' => 'OB002', 'id_jenis_tabung' => 2, 'id_status_tabung' => 1],
            ['id_tabung' => 5, 'kode_tabung' => 'NT001', 'id_jenis_tabung' => 3, 'id_status_tabung' => 1],
            ['id_tabung' => 6, 'kode_tabung' => 'NT002', 'id_jenis_tabung' => 3, 'id_status_tabung' => 1],
            ['id_tabung' => 7, 'kode_tabung' => 'AR001', 'id_jenis_tabung' => 4, 'id_status_tabung' => 1],
            ['id_tabung' => 8, 'kode_tabung' => 'AR002', 'id_jenis_tabung' => 4, 'id_status_tabung' => 1],
            ['id_tabung' => 9, 'kode_tabung' => 'AC001', 'id_jenis_tabung' => 5, 'id_status_tabung' => 1],
            ['id_tabung' => 10, 'kode_tabung' => 'AC002', 'id_jenis_tabung' => 5, 'id_status_tabung' => 1],
            ['id_tabung' => 11, 'kode_tabung' => 'N2O01', 'id_jenis_tabung' => 6, 'id_status_tabung' => 1],
            ['id_tabung' => 12, 'kode_tabung' => 'N2O02', 'id_jenis_tabung' => 6, 'id_status_tabung' => 1],
        ]);
    }
}
