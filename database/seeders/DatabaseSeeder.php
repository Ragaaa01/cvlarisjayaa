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
        $this->call([
            RoleSeeder::class,
            AlamatSeeder::class,
            UserSeeder::class,
            JenisTabungSeeder::class,
            StatusTabungSeeder::class,
            KepemilikanSeeder::class,
            JenisTransaksiDetailSeeder::class,
            TabungSeeder::class,
            MitraSeeder::class,
            NotifikasiTemplateSeeder::class,
        ]);
    }
}
