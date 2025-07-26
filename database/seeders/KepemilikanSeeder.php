<?php

namespace Database\Seeders;

use App\Models\Kepemilikan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KepemilikanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Kepemilikan::firstOrCreate(['keterangan_kepemilikan' => 'milik_laris_jaya_gas']);
        Kepemilikan::firstOrCreate(['keterangan_kepemilikan' => 'milik_pelanggan']);
    }
}
