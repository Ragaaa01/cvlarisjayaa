<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AkunController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\OrangController;
use App\Http\Controllers\Web\TabungController;
use App\Http\Controllers\Web\PerusahaanController;
use App\Http\Controllers\Web\JenisTabungController;
use App\Http\Controllers\Web\StatusTabungController;
use App\Http\Controllers\Web\OrangPerusahaanController;

Route::get('/', fn () => view('welcome'))->name('welcome');

Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout')->name('logout');
});

Route::middleware(['auth', 'role:administrator,karyawan'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'adminDashboard'])->name('dashboard');
    // Data Orang
    Route::resource('orang', OrangController::class, ['parameters' => ['orang' => 'id']])->names('admin.orang');
    Route::get('/orang/export/excel', [OrangController::class, 'exportExcel'])->name('admin.orang.export.excel');
    Route::get('/orang/export/pdf', [OrangController::class, 'exportPdf'])->name('admin.orang.export.pdf');

    // Data Perusahaan
    Route::resource('perusahaan', PerusahaanController::class, ['parameters' => ['perusahaan' => 'id']])->names('admin.perusahaan');
    Route::get('/perusahaan/search', [PerusahaanController::class, 'search'])->name('admin.perusahaan.search');

    // Data Orang Perusahaan
    Route::resource('orang_perusahaan', OrangPerusahaanController::class, ['parameters' => ['orang_perusahaan' => 'id']])->names('admin.orang_perusahaan');

    // Data Role
    Route::resource('role', RoleController::class, ['parameters' => ['role' => 'id']])->names('admin.role');

    // Data Akun
    Route::resource('akun', AkunController::class, ['parameters' => ['akun' => 'id']])->names('admin.akun');
    
    //Data Jenis Tabung
    Route::resource('jenis_tabung', JenisTabungController::class, ['parameters' => ['jenis_tabung' => 'id']])->names('admin.jenis_tabung');
    
    
    //Data Status Tabung
    Route::resource('status_tabung', StatusTabungController::class, ['parameters' => ['status_tabung' => 'id']])->names('admin.status_tabung');
    
    //Data Status Tabung
    Route::resource('tabung', TabungController::class, ['parameters' => ['tabung' => 'id']])->names('admin.tabung');



});