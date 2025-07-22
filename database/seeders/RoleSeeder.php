<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            ['id_role' => 1, 'nama_role' => 'administrator'],
            ['id_role' => 2, 'nama_role' => 'pelanggan'],
            ['id_role' => 3, 'nama_role' => 'karyawan'],
        ]);
    }
}
