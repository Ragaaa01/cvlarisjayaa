<?php

namespace Database\Seeders;

use App\Models\StatusTabung;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusTabungSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StatusTabung::firstOrCreate(['status_tabung' => 'tersedia']);
        StatusTabung::firstOrCreate(['status_tabung' => 'dipinjam']);
        StatusTabung::firstOrCreate(['status_tabung' => 'rusak']);
        StatusTabung::firstOrCreate(['status_tabung' => 'hilang']);
    }
}
