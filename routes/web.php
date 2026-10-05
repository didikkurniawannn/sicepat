<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportReportController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PublicMonitorController;
use App\Http\Controllers\SectionUserController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('home');
// Pantauan publik per kecamatan (tanpa nominal) — /pantau lama dialihkan ke direktori wilayah
Route::get('/pantau', fn() => redirect('/#wilayah'));
Route::get('/pantau/{slug}', [PublicMonitorController::class, 'index'])->name('pantau');
Route::get('/api/pantau/{slug}/events', [PublicMonitorController::class, 'events']);
Route::get('/api/login-accounts', [AuthController::class, 'loginAccounts']);

// Installer (diproteksi token, otomatis nonaktif setelah sukses)
Route::get('/install', [InstallerController::class, 'index']);
Route::post('/install', [InstallerController::class, 'run']);
Route::post('/install/migrate', [InstallerController::class, 'migrateUp']);
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/kegiatan', [ActivityController::class, 'index']);
    Route::get('/kegiatan/tambah', [ActivityController::class, 'create']);
    Route::post('/kegiatan', [ActivityController::class, 'store']);
    Route::get('/kegiatan/{activity}', [ActivityController::class, 'show']);
    Route::get('/kegiatan/{activity}/edit', [ActivityController::class, 'edit']);
    Route::put('/kegiatan/{activity}', [ActivityController::class, 'update']);
    Route::delete('/kegiatan/{activity}', [ActivityController::class, 'destroy']);
    Route::post('/kegiatan/{activity}/ajukan', [ActivityController::class, 'ajukan']);
    Route::post('/kegiatan/{activity}/progress', [ActivityController::class, 'updateProgress']);
    Route::post('/kegiatan/{activity}/selesai', [ActivityController::class, 'selesaikan']);
    Route::post('/kegiatan/{activity}/dokumen', [ActivityController::class, 'uploadDoc']);

    Route::get('/kalender', [CalendarController::class, 'index']);
    Route::get('/api/kalender', [CalendarController::class, 'events']);

    Route::get('/verifikasi', [VerificationController::class, 'index']);
    Route::post('/verifikasi/{activity}', [VerificationController::class, 'decide']);

    Route::get('/import', [ImportReportController::class, 'showImport']);
    Route::post('/import/preview', [ImportReportController::class, 'preview']);
    Route::post('/import/proses', [ImportReportController::class, 'process']);
    Route::get('/import/template', [ImportReportController::class, 'template']);

    Route::get('/laporan', [ImportReportController::class, 'reports']);
    Route::get('/laporan/excel', [ImportReportController::class, 'exportExcel']);
    Route::get('/laporan/pdf', [ImportReportController::class, 'exportPdf']);
    Route::get('/laporan/h7', [ImportReportController::class, 'h7']);
    Route::get('/laporan/h7/excel', [ImportReportController::class, 'h7Excel']);
    Route::get('/laporan/h7/pdf', [ImportReportController::class, 'h7Pdf']);

    Route::get('/unit-kerja', [SectionUserController::class, 'sections']);
    Route::get('/unit-kerja/{section}', [SectionUserController::class, 'sectionShow']);

    Route::get('/pengguna', [SectionUserController::class, 'users']);
    Route::post('/pengguna', [SectionUserController::class, 'usersStore']);

    Route::get('/kecamatan', [TenantController::class, 'index']);
    Route::post('/kecamatan', [TenantController::class, 'store']);
    Route::post('/kecamatan/{kecamatan}/toggle', [TenantController::class, 'toggle']);

    Route::get('/notifikasi', [SectionUserController::class, 'notifications']);
});
