<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\Orang;
use App\Models\Tagihan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat data Administrator
        $administrator = Orang::create([
            'nama_lengkap' => 'Administrator Laris Jaya Gas',
            'nik' => '3200000000000000',
            'no_telepon' => '082119128578',
            'alamat' => 'Karangampel, Gang 2 Utara, Indramayu',
            'id_kelurahan' => 152,
        ]);

        Akun::create([
            'id_orang' => $administrator->id_orang,
            'id_role' => 1,
            'email' => 'administrator@larisjayagas.com',
            'password' => Hash::make('password123'),
            'status_aktif' => true,
        ]);

        // Buat data Karyawan
        $administrator = Orang::create([
            'nama_lengkap' => 'Karyawan Laris Jaya Gas',
            'nik' => '3200000000000002',
            'no_telepon' => '082119128512',
            'alamat' => 'Karangampel, Gang 2 Utara, Indramayu',
            'id_kelurahan' => 152,
        ]);

        Akun::create([
            'id_orang' => $administrator->id_orang,
            'id_role' => 3,
            'email' => 'karyawan@larisjayagas.com',
            'password' => Hash::make('password123'),
            'status_aktif' => true,
        ]);

        // 2. Buat data Pelanggan ""
        $pelanggan = Orang::create([
            'nama_lengkap' => 'Aditya Sukma Pratama',
            'nik' => '3210000000000001',
            'no_telepon' => '081111111111',
            'alamat' => 'Jl. Mawar No. 1, Indramayu',
            'id_kelurahan' => 97,
        ]);

        Akun::create([
            'id_orang' => $pelanggan->id_orang,
            'id_role' => 2, // ID untuk 'pelanggan'
            'email' => 'pelanggan@gmail.com',
            'password' => Hash::make('password123'),
            'status_aktif' => true,
        ]);
    }
}
