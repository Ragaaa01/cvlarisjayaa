<?php

use App\Http\Controllers\Api\Administrator\JenisTabungController;
use App\Http\Controllers\Api\Administrator\PelangganController;
use App\Http\Controllers\Api\Administrator\PengembalianController;
use App\Http\Controllers\Api\Administrator\PenyiapanPesananController;
use App\Http\Controllers\Api\Administrator\StatusTabungController;
use App\Http\Controllers\Api\Administrator\TabungController;
use App\Http\Controllers\Api\Administrator\TagihanController;
use App\Http\Controllers\Api\Administrator\TransaksiLangsungController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\FcmTokenController;
use App\Http\Controllers\Api\MidtransWebhookController;
use App\Http\Controllers\Api\Pelanggan\DepositController;
use App\Http\Controllers\Api\Pelanggan\PesananController;
use App\Http\Controllers\Api\Pelanggan\ProdukController;
use App\Http\Controllers\Api\Pelanggan\ProfilController;
use App\Http\Controllers\Api\Pelanggan\RiwayatController;
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

// Route yang membutuhkan otentikasi
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/fcm-token', [FcmTokenController::class, 'store']);

    // Route Management Administrator
    Route::prefix('/administrator')->middleware(['auth:sanctum'])->group(function () {

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

        // Route untuk Tabung
        Route::get('/tabung', [TabungController::class, 'index']);
        Route::post('/tabung', [TabungController::class, 'store']);
        Route::get('/tabung/{id}', [TabungController::class, 'show']);
        Route::get('/tabung/kode/{kode_tabung}', [TabungController::class, 'showByKode']);
        Route::put('/tabung/{id}', [TabungController::class, 'update']);
        Route::delete('/tabung/{id}', [TabungController::class, 'destroy']);

        // Route untuk Pelanggan
        Route::get('/pelanggan', [PelangganController::class, 'index']);
        Route::post('/pelanggan', [PelangganController::class, 'store']);
        Route::get('/pelanggan/{id}', [PelangganController::class, 'show']);
        Route::put('/pelanggan/{id}', [PelangganController::class, 'update']);
        Route::post('/pelanggan/{id}/aktivasi', [PelangganController::class, 'aktivasi']);

        // Route untuk Transaksi Langsung
        Route::post('/transaksi-langsung', [TransaksiLangsungController::class, 'store']);

        // Route untuk Penyiapan Pesanan
        Route::get('/peminjaman/menunggu-penyiapan', [PenyiapanPesananController::class, 'index']);
        Route::post('/peminjaman/{id_peminjaman}/siapkan', [PenyiapanPesananController::class, 'siapkan']);

        // Route untuk Tagihan
        Route::get('/tagihan', [TagihanController::class, 'index']);
        Route::get('/tagihan/{id_tagihan}', [TagihanController::class, 'show']);
        Route::post('/tagihan/{id_tagihan}/konfirmasi-tunai', [TagihanController::class, 'konfirmasiPembayaranTunai']);

        // Route untuk Pengembalian
        Route::post('/pengembalian/{id_peminjaman}', [PengembalianController::class, 'store']);
    });

    // Route Management Pelanggan
    Route::prefix('pelanggan')->middleware('auth:sanctum')->group(function () {

        // Route untuk melihat katalog produk (jenis tabung)
        Route::get('/produk', [ProdukController::class, 'index']);

        // Route untuk membuat pesanan peminjaman
        Route::post('/pesanan/peminjaman', [PesananController::class, 'buatPesanan']);

        Route::post('/deposit/top-up', [DepositController::class, 'topUp']);

        // Grup untuk semua rute riwayat
        Route::prefix('riwayat')->group(function () {
            Route::get('/peminjaman', [RiwayatController::class, 'peminjaman']);
            Route::get('/isi-ulang', [RiwayatController::class, 'isiUlang']);
            Route::get('/tagihan', [RiwayatController::class, 'tagihan']);
            Route::get('/deposit', [RiwayatController::class, 'deposit']);
        });

        Route::prefix('profil')->group(function () {
            Route::get('/', [ProfilController::class, 'show']);
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
