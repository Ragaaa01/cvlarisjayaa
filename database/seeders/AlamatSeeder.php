<?php

namespace Database\Seeders;

use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Provinsi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AlamatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Membuat data Provinsi
        $provinsi = Provinsi::firstOrCreate(['nama_provinsi' => 'Jawa Barat']);

        // Membuat data Kabupaten
        $kabupaten = Kabupaten::firstOrCreate(
            ['nama_kabupaten' => 'Indramayu'],
            ['id_provinsi' => $provinsi->id_provinsi]
        );

        // Data Kecamatan di Indramayu
        $kecamatans = [
            'Anjatan',
            'Arahan',
            'Balongan',
            'Bangodua',
            'Bongas',
            'Cantigi',
            'Cikedung',
            'Gabuswetan',
            'Gantar',
            'Haurgeulis',
            'Indramayu',
            'Jatibarang',
            'Juntinyuat',
            'Kandanghaur',
            'Karangampel',
            'Kedokan Bunder',
            'Kertasemaya',
            'Krangkeng',
            'Kroya',
            'Lelea',
            'Lohbener',
            'Losarang',
            'Pasekan',
            'Patrol',
            'Sindang',
            'Sliyeg',
            'Sukagumiwang',
            'Sukra',
            'Terisi',
            'Tukdana',
            'Widasari'
        ];

        foreach ($kecamatans as $nama_kecamatan) {
            Kecamatan::firstOrCreate(
                ['nama_kecamatan' => $nama_kecamatan, 'id_kabupaten' => $kabupaten->id_kabupaten]
            );
        }
    }
}
