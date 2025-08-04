<?php

use App\Http\Controllers\Api\Administrator\DashboardController;
use App\Http\Controllers\Api\Administrator\JenisTabungController;
use App\Http\Controllers\Api\Administrator\KepemilikanController;
use App\Http\Controllers\Api\Administrator\MitraController;
use App\Http\Controllers\Api\Administrator\OrangMitraController;
use App\Http\Controllers\Api\Administrator\PelangganController;
use App\Http\Controllers\Api\Administrator\PembayaranController;
use App\Http\Controllers\Api\Administrator\PengembalianController;
use App\Http\Controllers\Api\Administrator\PengembalianStrukController;
use App\Http\Controllers\Api\Administrator\PenyiapanPesananController;
use App\Http\Controllers\Api\Administrator\PesananAdministratorController;
use App\Http\Controllers\Api\Administrator\ProfilController;
use App\Http\Controllers\Api\Administrator\StatusTabungController;
use App\Http\Controllers\Api\Administrator\TabungController;
use App\Http\Controllers\Api\Administrator\TabungDetailController;
use App\Http\Controllers\Api\Administrator\TagihanController;
use App\Http\Controllers\Api\Administrator\TransaksiLangsungController;
use App\Http\Controllers\Api\AlamatController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\FcmTokenController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\MidtransWebhookController;
use App\Http\Controllers\Api\Pelanggan\DashboardPelangganController;
use App\Http\Controllers\Api\Pelanggan\DepositController;
use App\Http\Controllers\Api\Pelanggan\NotifikasiPelangganController;
use App\Http\Controllers\Api\Pelanggan\PesananController;
use App\Http\Controllers\Api\Pelanggan\ProdukController;
use App\Http\Controllers\Api\Pelanggan\ProfilPelangganController;
use App\Http\Controllers\Api\Pelanggan\RiwayatController;
use App\Http\Controllers\Api\Pelanggan\TagihanPelangganController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;








/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/midtrans/webhook', [MidtransWebhookController::class, 'handle']);

Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [ForgotPasswordController::class, 'reset']);

// Route Alamat 
Route::get('/alamat/kecamatan', [AlamatController::class, 'getKecamatans']);
Route::get('/alamat/kelurahan/{id_kecamatan}', [AlamatController::class, 'getKelurahansByKecamatan']);

// Route yang membutuhkan otentikasi
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/fcm-token', [FcmTokenController::class, 'store']);

    // Route Management Administrator
    Route::prefix('/administrator')->middleware(['auth:sanctum'])->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'getStats']);

        Route::get('/profil', [ProfilController::class, 'show']);
        Route::put('/profil', [ProfilController::class, 'update']);
        Route::post('/ubah-password', [ProfilController::class, 'ubahPassword']);

        // Route untuk Jenis Tabung
        Route::get('/jenis-tabung', [JenisTabungController::class, 'index']);
        Route::post('/jenis-tabung', [JenisTabungController::class, 'store']);
        Route::get('/jenis-tabung/{id}', [JenisTabungController::class, 'show']);
        Route::put('/jenis-tabung/{id}', [JenisTabungController::class, 'update']);
        Route::delete('/jenis-tabung/{id}', [JenisTabungController::class, 'destroy']);
        Route::post('/jenis-tabung/{id}/restore', [JenisTabungController::class, 'restore']);
        Route::delete('/jenis-tabung/{id}/force-delete', [JenisTabungController::class, 'forceDestroy']);

        // Route untuk Status Tabung
        Route::get('/status-tabung', [StatusTabungController::class, 'index']);

        // Rute untuk mengambil data master kepemilikan
        Route::get('/kepemilikan', [KepemilikanController::class, 'index']);

        // Route untuk Tabung
        Route::get('/tabung', [TabungController::class, 'index']);
        Route::post('/tabung', [TabungController::class, 'store']);
        Route::get('/tabung/{id}', [TabungController::class, 'show']);
        Route::get('/tabung/kode/{kode_tabung}', [TabungController::class, 'showByKode']);
        Route::put('/tabung/{id}', [TabungController::class, 'update']);
        Route::delete('/tabung/{id}', [TabungController::class, 'destroy']);

        Route::get('/tabung/{id_tabung}/download-qr', [TabungDetailController::class, 'downloadQrPdf']);

        // Route untuk Pelanggan
        Route::get('/pelanggan', [PelangganController::class, 'index']);
        Route::post('/pelanggan', [PelangganController::class, 'store']);
        Route::get('/pelanggan/aktif', [PelangganController::class, 'getPelangganAktif']);
        Route::get('/pelanggan/{id}', [PelangganController::class, 'show']);
        Route::put('/pelanggan/{id}', [PelangganController::class, 'update']);
        Route::post('/pelanggan/{id}/aktivasi', [PelangganController::class, 'aktivasi']);

        // Route untuk Mitra
        Route::get('/mitra', [MitraController::class, 'index']);
        Route::post('/mitra', [MitraController::class, 'store']);
        Route::get('/mitra/{id_mitra}', [MitraController::class, 'show']);
        Route::put('/mitra/{id_mitra}', [MitraController::class, 'update']);
        Route::delete('/mitra/{id_mitra}', [MitraController::class, 'destroy']);
        Route::post('/mitra/{id_mitra}/verify', [MitraController::class, 'verify']);

        // Route untuk Manajemen Hubungan Orang & Mitra
        Route::post('/orang-mitra', [OrangMitraController::class, 'store']);
        Route::delete('/orang-mitra/{id_orang_mitra}', [OrangMitraController::class, 'destroy']);

        // Route untuk Transaksi Langsung
        Route::post('/transaksi-langsung', [TransaksiLangsungController::class, 'store']);

        // Route untuk mencatat pembayaran tunai
        Route::post('/pembayaran/tunai', [PembayaranController::class, 'storeTunai']);

        // Route untuk memproses pengembalian tabung
        Route::post('/pengembalian', [PengembalianController::class, 'store']);
        Route::get('/pengembalian/aktif/{id_orang}', [PengembalianController::class, 'getPeminjamanAktifByOrang']);
        Route::post('/pengembalian/struk-pdf', [PengembalianStrukController::class, 'downloadStrukPdf']);

        // Route untuk proses pesanan 
        Route::get('/pesanan/menunggu-penyiapan', [PesananAdministratorController::class, 'getMenungguPenyiapan']);
        Route::get('/pesanan/{id_transaksi}', [PesananAdministratorController::class, 'show']); // Rute Detail
        Route::post('/pesanan/{id_transaksi}/siapkan', [PesananAdministratorController::class, 'siapkanPesanan']);
    });

    // Route Management Pelanggan
    Route::prefix('pelanggan')->middleware('auth:sanctum')->group(function () {

        // Route untuk dashboard pelanggan
        Route::get('/dashboard', [DashboardPelangganController::class, 'index']);
        Route::get('/profil', [ProfilPelangganController::class, 'show']);

        Route::get('/produk', [ProdukController::class, 'index']);
        Route::post('/pesanan', [PesananController::class, 'store']);

        Route::get('/riwayat/peminjaman', [RiwayatController::class, 'peminjaman']);

        Route::get('/tagihan/rekapitulasi', [TagihanPelangganController::class, 'getRekapitulasi']);
        Route::post('/tagihan/bayar', [TagihanPelangganController::class, 'bayar']);

        Route::get('/notifikasi', [NotifikasiPelangganController::class, 'index']);
        Route::post('/notifikasi/{id_notifikasi}/baca', [NotifikasiPelangganController::class, 'tandaiDibaca']);

        // Grup untuk semua rute riwayat
        Route::prefix('riwayat')->group(function () {
            Route::get('/isi-ulang', [RiwayatController::class, 'isiUlang']);
            Route::get('/tagihan', [RiwayatController::class, 'tagihan']);
            Route::get('/deposit', [RiwayatController::class, 'deposit']);
        });

        Route::prefix('profil')->group(function () {
            // Route::get('/', [ProfilController::class, 'show']);
            Route::put('/', [ProfilController::class, 'update']);
            Route::post('/ubah-password', [ProfilController::class, 'ubahPassword']);
        });
    });

    // Route Management Karyawan
    Route::prefix('karyawan')->middleware('auth:sanctum')->group(function () {
        // Tambahkan rute yang memerlukan otentikasi di sini
        Route::get('/profile', [AuthController::class, 'profile']);
        // ... rute lain yang memerlukan otentikasi ...
    });
});
