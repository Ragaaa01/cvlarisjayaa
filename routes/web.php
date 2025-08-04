<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AkunController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\MitraController;
use App\Http\Controllers\Web\OrangController;
use App\Http\Controllers\Web\TabungController;
use App\Http\Controllers\Web\OrangMitraController;
use App\Http\Controllers\Web\JenisTabungController;
use App\Http\Controllers\Web\StatusTabungController;
use App\Http\Controllers\Web\JenisTransaksiController;
use App\Http\Controllers\Web\KepemilikanTabungController;
use App\Http\Controllers\Web\TransaksiController;
use App\Http\Controllers\Web\DetailTransaksiController;
use App\Http\Controllers\Web\PengembalianController;
use App\Http\Controllers\Web\PembayaranController;

Route::get('/', fn () => view('welcome'))->name('welcome');

Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout')->name('logout');
});

Route::middleware(['auth', 'role:administrator'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'adminDashboard'])->name('dashboard');

    // Data Orang
    Route::resource('orang', OrangController::class, ['parameters' => ['orang' => 'id_orang']])->names('admin.orang');
    Route::get('/orang/export/excel', [OrangController::class, 'exportExcel'])->name('admin.orang.export.excel');
    Route::get('/orang/export/pdf', [OrangController::class, 'exportPdf'])->name('admin.orang.export.pdf');

    // Data Perusahaan
    Route::resource('mitra', MitraController::class, ['parameters' => ['mitra' => 'id_mitra']])->names('admin.mitra');
    Route::get('/mitra/export/excel', [MitraController::class, 'exportExcel'])->name('admin.mitra.export.excel');
    Route::get('/mitra/export/pdf', [MitraController::class, 'exportPdf'])->name('admin.mitra.export.pdf');

    // Data Orang Perusahaan
    Route::resource('orang_mitra', OrangMitraController::class, ['parameters' => ['orang_mitra' => 'id_orang_mitra']])->names('admin.orang_mitra');
    Route::get('/orang_mitra/export/excel', [OrangMitraController::class, 'exportExcel'])->name('admin.orang_mitra.export.excel');
    Route::get('/orang_mitra/export/pdf', [OrangMitraController::class, 'exportPdf'])->name('admin.orang_mitra.export.pdf');
    Route::get('/orang_mitra/check-status-valid', [OrangMitraController::class, 'checkStatusValid'])->name('admin.orang_mitra.checkStatusValid');

    // Data Role
    Route::resource('role', RoleController::class, ['parameters' => ['role' => 'id_role']])->names('admin.role');
    Route::get('/role/export/excel', [RoleController::class, 'exportExcel'])->name('admin.role.export.excel');
    Route::get('/role/export/pdf', [RoleController::class, 'exportPdf'])->name('admin.role.export.pdf');

    // Data Akun
    Route::resource('akun', AkunController::class, ['parameters' => ['akun' => 'id_akuns']])->names('admin.akun');
    Route::get('/akun/export/excel', [AkunController::class, 'exportExcel'])->name('admin.akun.export.excel');
    Route::get('/akun/export/pdf', [AkunController::class, 'exportPdf'])->name('admin.akun.export.pdf');

    // Data Jenis Tabung
    Route::resource('jenis_tabung', JenisTabungController::class, ['parameters' => ['jenis_tabung' => 'id_jenis_tabung']])->names('admin.jenis_tabung');
    Route::get('/jenis_tabung/export/excel', [JenisTabungController::class, 'exportExcel'])->name('admin.jenis_tabung.export.excel');
    Route::get('/jenis_tabung/export/pdf', [JenisTabungController::class, 'exportPdf'])->name('admin.jenis_tabung.export.pdf');

    // Data Status Tabung
    Route::resource('status_tabung', StatusTabungController::class, ['parameters' => ['status_tabung' => 'id_status_tabung']])->names('admin.status_tabung');
    Route::get('/status_tabung/export/excel', [StatusTabungController::class, 'exportExcel'])->name('admin.status_tabung.export.excel');
    Route::get('/status_tabung/export/pdf', [StatusTabungController::class, 'exportPdf'])->name('admin.status_tabung.export.pdf');

    // Data Tabung
    Route::resource('tabung', TabungController::class, ['parameters' => ['tabung' => 'id_tabung']])->names('admin.tabung');
    Route::get('/tabung/export/excel', [TabungController::class, 'exportExcel'])->name('admin.tabung.export.excel');
    Route::get('/tabung/export/pdf', [TabungController::class, 'exportPdf'])->name('admin.tabung.export.pdf');
    Route::get('/admin/tabung/{id}/history', [TabungController::class, 'history'])->name('admin.tabung.history'); 
    // Data Kepemilikan Tabung
    Route::resource('kepemilikan_tabung', KepemilikanTabungController::class, ['parameters' => ['kepemilikan_tabung' => 'id_kepemilikan']])->names('admin.kepemilikan_tabung');
    Route::get('/kepemilikan_tabung/export/excel', [KepemilikanTabungController::class, 'exportExcel'])->name('admin.kepemilikan_tabung.export.excel');
    Route::get('/kepemilikan_tabung/export/pdf', [KepemilikanTabungController::class, 'exportPdf'])->name('admin.kepemilikan_tabung.export.pdf');

    // Data Pendukung Transaksi
    Route::resource('jenis_transaksi', JenisTransaksiController::class, ['parameters' => ['jenis_transaksi' => 'id_jenis_transaksi_detail']])->names('admin.jenis_transaksi');
    Route::get('/jenis_transaksi/export/excel', [JenisTransaksiController::class, 'exportExcel'])->name('admin.jenis_transaksi.export.excel');
    Route::get('/jenis_transaksi/export/pdf', [JenisTransaksiController::class, 'exportPdf'])->name('admin.jenis_transaksi.export.pdf');
});

Route::middleware(['auth', 'role:administrator,karyawan,pelanggan'])->group(function () {
    // Data Transaksi
    Route::resource('transaksi', TransaksiController::class, ['parameters' => ['transaksi' => 'id_transaksi']])->names('transaksi');
    Route::get('/transaksi/export/excel', [TransaksiController::class, 'exportExcel'])->name('transaksi.export.excel');
    Route::get('/transaksi/export/pdf', [TransaksiController::class, 'exportPdf'])->name('transaksi.export.pdf');
   
    // Pembayaran
    Route::resource('pembayaran', PembayaranController::class, ['parameters' => ['pembayaran' => 'id_pembayaran']])->only(['index', 'update', 'show'])->names('pembayaran');

    // Pengembalian
    Route::resource('pengembalian', PengembalianController::class, ['parameters' => ['pengembalian' => 'id_pengembalian']])->only(['index', 'show', 'update'])->names('pengembalian');
});