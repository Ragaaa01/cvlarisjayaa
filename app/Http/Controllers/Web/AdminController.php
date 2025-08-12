<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Tabung;
use App\Models\Pengembalian;
use App\Models\Orang;
use App\Models\Mitra;
use App\Models\Pembayaran;
use App\Models\StatusTabung;
use App\Models\Kepemilikan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function adminDashboard()
    {
        try {
            // Total Data Tabung
            $totalTubes = Tabung::count();

            // Tabung Dipinjam (status tabung 'dipinjam')
            $statusDipinjam = StatusTabung::where('status_tabung', 'dipinjam')->first();
            $borrowedTubes = $statusDipinjam ? Tabung::where('id_status_tabung', $statusDipinjam->id_status_tabung)->count() : 0;

            // Tabung Tersedia (status tabung 'tersedia')
            $statusTersedia = StatusTabung::where('status_tabung', 'tersedia')->first();
            $availableTubes = $statusTersedia ? Tabung::where('id_status_tabung', $statusTersedia->id_status_tabung)->count() : 0;

            // Validasi konsistensi: Pastikan total tabung sesuai dengan jumlah status
            $totalByStatus = Tabung::groupBy('id_status_tabung')->selectRaw('id_status_tabung, count(*) as total')->pluck('total', 'id_status_tabung')->sum();
            if ($totalTubes !== $totalByStatus) {
                Log::warning('Ketidaksesuaian jumlah tabung: totalTubes=' . $totalTubes . ', totalByStatus=' . $totalByStatus);
            }

            // Pelanggan Perorangan (hanya yang memiliki transaksi valid)
            $individualCustomers = Orang::whereHas('transaksis', function ($query) {
                $query->where('status_valid', true);
            })->count();

            // Pelanggan Perusahaan
            $companyCustomers = Mitra::count();

            // Peminjaman Berlangsung
            $ongoingLoans = Pengembalian::whereNull('tanggal_pengembalian')->count();

            // Tagihan Berlangsung
            $ongoingInvoices = Pembayaran::whereNull('tanggal_pembayaran')->count();

            return view('admin.pages.index', compact(
                'totalTubes',
                'borrowedTubes',
                'availableTubes',
                'individualCustomers',
                'companyCustomers',
                'ongoingLoans',
                'ongoingInvoices'
            ));
        } catch (\Exception $e) {
            Log::error('Gagal memuat data dashboard: ' . $e->getMessage());
            return view('Admin.pages.index')->with('error', 'Gagal memuat data dashboard. Silakan coba lagi.');
        }
    }
}