<?php

namespace Database\Seeders;

use App\Models\Role;
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
        Role::firstOrCreate(['nama_role' => 'administrator']);
        Role::firstOrCreate(['nama_role' => 'pelanggan']);
        Role::firstOrCreate(['nama_role' => 'karyawan']);
    }
}
