<?php

namespace Database\Seeders;

use App\Models\Tabung;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TabungSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Daftar jenis tabung dengan kode awalan
        $jenisTabungs = [
            ['id_jenis_tabung' => 1, 'kode' => 'OK', 'nama' => 'Oksigen Kecil O2 m³'],
            ['id_jenis_tabung' => 2, 'kode' => 'OB', 'nama' => 'Oksigen Besar O2 6m³'],
            ['id_jenis_tabung' => 3, 'kode' => 'N', 'nama' => 'Nitrogen'],
            ['id_jenis_tabung' => 4, 'kode' => 'AR', 'nama' => 'Argon'],
            ['id_jenis_tabung' => 5, 'kode' => 'AC', 'nama' => 'Acetelyne'],
            ['id_jenis_tabung' => 6, 'kode' => 'N2O', 'nama' => 'Dinitrogen Monoksida N2O'],
        ];

        // ID kepemilikan untuk "milik laris jaya gas"
        $idKepemilikan = 1; // Berdasarkan KepemilikanSeeder
        // ID status tabung untuk "tersedia"
        $idStatusTabung = 1; // Berdasarkan StatusTabungSeeder

        // Loop untuk setiap jenis tabung
        foreach ($jenisTabungs as $jenis) {
            // Membuat 20 tabung untuk setiap jenis
            for ($i = 1; $i <= 20; $i++) {
                // Format kode tabung (misalnya, OK01, OK02, ..., OK20)
                $kodeTabung = sprintf('%s%02d', $jenis['kode'], $i);

                Tabung::firstOrCreate(
                    ['kode_tabung' => $kodeTabung],
                    [
                        'id_jenis_tabung' => $jenis['id_jenis_tabung'],
                        'id_status_tabung' => $idStatusTabung,
                        'id_kepemilikan' => $idKepemilikan,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
