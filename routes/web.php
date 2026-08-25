<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;

// AUTHENTICATION
Route::get('/login',[AuthController::class,'login'])
    ->name('login');

Route::post('/login',[AuthController::class,'process'])
    ->name('login.process');

Route::post('/logout',[AuthController::class,'logout'])
    ->name('logout');


Route::middleware('auth')->group(function () {
    // DASHBOARD
    Route::get('/', [DashboardController::class, 'index']);

    Route::get('/data-alat', [DashboardController::class, 'dataAlat'])
        ->name('data-alat');

    Route::get('/alat/{nama}', [DashboardController::class, 'detail'])
    ->name('alat.detail');


    // UPLOAD
    Route::middleware('role:admin')->group(function () {

        Route::get('/upload', [DashboardController::class,'uploadForm'])
            ->name('upload');

        Route::post('/upload', [DashboardController::class,'upload']);

    });


    // REPORT / LAPORAN
    Route::get('/laporan', [ReportController::class, 'index'])
        ->name('laporan');

    Route::resource('users', UserController::class)
        ->except('show')
        ->middleware('role:admin');

    // EXPORT
    Route::prefix('export')->group(function () {

        Route::get('/pdf',
            [ExportController::class,'exportPDF'])
            ->name('export.pdf');

        Route::get('/excel',
            [ExportController::class,'exportExcel'])
            ->name('export.excel');

    });
            
    // EXPORT DARI DETAIL
    Route::get('/detail/{nama}/export-pdf',
        [ExportController::class,'exportDetailPDF'])
        ->name('detail.export.pdf');
});