<?php

namespace Database\Seeders;

use App\Models\Kelurahan;
use App\Models\Mitra;
use Illuminate\Database\Seeder;

class MitraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Daftar nama mitra realistis
        $namaMitras = [
            'Toko Gas Jaya Makmur',
            'Agen Gas Sejahtera',
            'Gas Indramayu Sentosa',
            'Toko Gas Berkah',
            'Agen Gas Mulya',
            'Gas Anjatan Jaya',
            'Toko Gas Amanah',
            'Agen Gas Barokah',
            'Gas Karangampel Maju',
            'Toko Gas Harmoni',
            'Agen Gas Sumber Rejeki',
            'Gas Indramayu Baru',
            'Toko Gas Nusantara',
            'Agen Gas Mitra Sejati',
            'Gas Sukses Jaya',
        ];

        // Ambil semua id_kelurahan dari tabel kelurahans
        $kelurahans = Kelurahan::pluck('id_kelurahan', 'nama_kelurahan')->toArray();

        // Pastikan ada data kelurahan
        if (empty($kelurahans)) {
            throw new \Exception('Tabel kelurahans kosong. Jalankan AlamatSeeder terlebih dahulu.');
        }

        // Loop untuk membuat 15 mitra
        foreach ($namaMitras as $index => $namaMitra) {
            // Pilih kelurahan secara acak
            $namaKelurahan = array_rand($kelurahans);
            $idKelurahan = $kelurahans[$namaKelurahan];

            // Buat alamat mitra berdasarkan nama kelurahan
            $alamatMitra = 'Jl. Raya ' . $namaKelurahan . ' No. ' . ($index + 1);

            Mitra::firstOrCreate(
                ['nama_mitra' => $namaMitra],
                [
                    'id_kelurahan' => $idKelurahan,
                    'alamat_mitra' => $alamatMitra,
                    'verified' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}