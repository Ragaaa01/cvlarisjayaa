<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Akun;
use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       // Tambah role
        Role::create(['nama_role' => 'administrator']);
        Role::create(['nama_role' => 'karyawan']);
        Role::create(['nama_role' => 'pelanggan']);

        // Tambah akun admin
        Akun::create([
            'id_role' => 1, // ID untuk role administrator
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
            'status_aktif' => true,
        ]);
    }
}
