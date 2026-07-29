<?php

use App\Http\Controllers\Api\AsetController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\MasterDataController;
use App\Http\Controllers\Api\OpnameController;
use App\Http\Controllers\Api\PersediaanController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('api.token')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/aset/ringkas', [AsetController::class, 'ringkas']);
    Route::get('/aset/jenis/{jenisBarang}/unit', [AsetController::class, 'unit']);
    Route::apiResource('/aset', AsetController::class)->except(['index']);
    Route::get('/aset/{aset}/riwayat', [AsetController::class, 'riwayat']);

    Route::get('/persediaan/ringkas', [PersediaanController::class, 'ringkas']);
    Route::get('/persediaan/jenis/{jenisBarang}/detail', [PersediaanController::class, 'detailByJenis']);
    Route::post('/persediaan/{persediaan}/barang-masuk', [PersediaanController::class, 'barangMasuk']);
    Route::post('/persediaan/{persediaan}/pengajuan-keluar', [PersediaanController::class, 'pengajuanKeluar']);
    Route::get('/persediaan/pengajuan', [PersediaanController::class, 'pengajuan']);
    Route::post('/persediaan/pengajuan/{transaksi}/setujui', [PersediaanController::class, 'setujui']);
    Route::post('/persediaan/pengajuan/{transaksi}/tolak', [PersediaanController::class, 'tolak']);
    Route::get('/persediaan/{persediaan}/batch', [PersediaanController::class, 'batch']);
    Route::apiResource('/persediaan', PersediaanController::class)->only(['store', 'update']);

    Route::get('/opname/ruangan/{ruangan}', [OpnameController::class, 'ruangan']);
    Route::post('/opname', [OpnameController::class, 'store']);
    Route::get('/opname/riwayat', [OpnameController::class, 'riwayat']);

    Route::get('/laporan/baop', [LaporanController::class, 'baop']);
    Route::get('/laporan/dbr', [LaporanController::class, 'dbr']);
    Route::get('/laporan/nilai-buku', [LaporanController::class, 'nilaiBuku']);
    Route::get('/laporan/export', [LaporanController::class, 'export']);

    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);

    Route::get('/ruangan', [MasterDataController::class, 'ruangan']);
    Route::get('/kategori', [MasterDataController::class, 'kategori']);
});
