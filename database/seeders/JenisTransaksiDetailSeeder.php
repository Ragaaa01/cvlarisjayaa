<?php

namespace Database\Seeders;

use App\Models\JenisTransaksiDetail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JenisTransaksiDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        JenisTransaksiDetail::firstOrCreate(['jenis_transaksi' => 'peminjaman']);
        JenisTransaksiDetail::firstOrCreate(['jenis_transaksi' => 'isi ulang']);
        JenisTransaksiDetail::firstOrCreate(['jenis_transaksi' => 'deposit']);
    }
}
