<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\Kelurahan;
use App\Models\Orang;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua id_kelurahan dari tabel kelurahans
        $kelurahans = Kelurahan::pluck('id_kelurahan', 'nama_kelurahan')->toArray();

        // Pastikan tabel kelurahans tidak kosong
        if (empty($kelurahans)) {
            throw new \Exception('Tabel kelurahans kosong. Jalankan AlamatSeeder terlebih dahulu.');
        }

        // 1. Buat data Administrator
        $administrator = Orang::create([
            'nama_lengkap' => 'Administrator Laris Jaya Gas',
            'nik' => '3200000000000000',
            'no_telepon' => '082119128578',
            'alamat' => 'Karangampel, Gang 2 Utara, Indramayu',
            'id_kelurahan' => array_key_exists('Karangampel', $kelurahans) ? $kelurahans['Karangampel'] : reset($kelurahans),
        ]);

        Akun::create([
            'id_orang' => $administrator->id_orang,
            'id_role' => 1,
            'email' => 'administrator@larisjayagas.com',
            'password' => Hash::make('password123'),
            'status_aktif' => true,
        ]);

        // 2. Buat data Karyawan
        $karyawan = Orang::create([
            'nama_lengkap' => 'Karyawan Laris Jaya Gas',
            'nik' => '3200000000000002',
            'no_telepon' => '082119128512',
            'alamat' => 'Karangampel, Gang 2 Utara, Indramayu',
            'id_kelurahan' => array_key_exists('Karangampel', $kelurahans) ? $kelurahans['Karangampel'] : reset($kelurahans),
        ]);

        Akun::create([
            'id_orang' => $karyawan->id_orang,
            'id_role' => 3,
            'email' => 'karyawan@larisjayagas.com',
            'password' => Hash::make('password123'),
            'status_aktif' => true,
        ]);

        // 3. Buat 20 data Pelanggan
        $namaPelanggans = [
            'Budi Santoso', 'Siti Aisyah', 'Ahmad Fauzi', 'Rina Wulandari', 'Eko Prasetyo',
            'Dewi Lestari', 'Hadi Susanto', 'Nurul Hidayah', 'Agus Setiawan', 'Lina Marlina',
            'Joko Widodo', 'Sri Mulyani', 'Dedi Kurniawan', 'Fitri Rahayu', 'Tono Hartono',
            'Yuni Sari', 'Rudi Hermawan', 'Mira Andini', 'Fajar Nugroho', 'Anita Permata',
        ];

        foreach ($namaPelanggans as $index => $nama) {
            // Generate NIK unik (321 + 13 digit acak)
            $nik = '321' . mt_rand(1000000000000, 9999999999999);
            
            // Generate nomor telepon unik
            $noTelepon = '08' . mt_rand(100000000, 999999999);
            
            // Pilih kelurahan secara acak
            $namaKelurahan = array_rand($kelurahans);
            $idKelurahan = $kelurahans[$namaKelurahan];
            
            // Buat alamat berdasarkan kelurahan
            $alamat = 'Jl. ' . $namaKelurahan . ' No. ' . ($index + 1);
            
            // Buat data pelanggan di tabel orangs
            $pelanggan = Orang::create([
                'nama_lengkap' => $nama,
                'nik' => $nik,
                'no_telepon' => $noTelepon,
                'alamat' => $alamat,
                'id_kelurahan' => $idKelurahan,
            ]);

            // Buat akun pelanggan di tabel akuns
            Akun::create([
                'id_orang' => $pelanggan->id_orang,
                'id_role' => 2, // ID untuk pelanggan
                'email' => 'pelanggan' . ($index + 1) . '@gmail.com',
                'password' => Hash::make('password123'),
                'status_aktif' => true,
            ]);
        }
    }
}