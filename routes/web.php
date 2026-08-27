<?php

// Admin
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\AttendanceReportController as AdminAttendanceReportController;

use App\Http\Controllers\Admin\ChangelogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InstituteController;
use App\Http\Controllers\Admin\InternshipController;
use App\Http\Controllers\Admin\ParticipantController;
use App\Http\Controllers\Admin\PermohonanController;
use App\Http\Controllers\Admin\SupervisorController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Atasan\DashboardController as AtasanDashboardController;
use App\Http\Controllers\Atasan\InstituteController as AtasanInstituteController;
use App\Http\Controllers\Atasan\InternshipController as AtasanInternshipController;
use App\Http\Controllers\Atasan\ParticipantController as AtasanParticipantController;
use App\Http\Controllers\Atasan\PermohonanController as AtasanPermohonanController;
use App\Http\Controllers\Atasan\SupervisorController as AtasanSupervisorController;
use App\Http\Controllers\Atasan\TaskController as AtasanTaskController;
use App\Http\Controllers\ParticipantRegistrationController;
use App\Http\Controllers\Pemagang\AttendanceController as PemagangAttendanceController;
use App\Http\Controllers\Pemagang\DashboardController as PemagangDashboardController;
use App\Http\Controllers\Pemagang\LogbookController;
use App\Http\Controllers\Pembimbing\DashboardController as PembimbingDashboardController;
use App\Http\Controllers\Pembimbing\ParticipantController as PembimbingParticipantController;
use App\Http\Controllers\Pembimbing\TaskController as PembimbingTaskController;
use App\Http\Controllers\Pembimbing\TaskOverviewController as PembimbingTaskOverviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pemagang\DeviceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Halaman utama → jika sudah login, redirect ke /dashboard; kalau belum, tampilkan login
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return view('auth.login');
});

// Dashboard umum (redirect per-role di RedirectController)
Route::get('/dashboard', [RedirectController::class, 'dashboard'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/register-participant', [ParticipantRegistrationController::class, 'create'])->name('participant.register');
Route::post('/register-participant', [ParticipantRegistrationController::class, 'store'])->name('participant.store');

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

        // Permohonan
        Route::get('/permohonan/data', [PermohonanController::class, 'data'])->name('permohonan.data');
        Route::resource('permohonan', PermohonanController::class)
            ->names('permohonan')
            ->parameters(['permohonan' => 'application']);
        Route::patch('/permohonan/{application}/status', [PermohonanController::class, 'updateStatus'])->name('permohonan.status');
        Route::get('/permohonan/export/excel', [PermohonanController::class, 'exportExcel'])->name('permohonan.export.excel');
        Route::get('/permohonan/export/pdf', [PermohonanController::class, 'exportPdf'])->name('permohonan.export.pdf');

        // Peserta
        Route::get('/peserta/data', [ParticipantController::class, 'data'])->name('peserta.data');
        Route::resource('peserta', ParticipantController::class)
            ->names('peserta')
            ->parameters(['peserta' => 'participant']);
        Route::get('/peserta/{participant}/approve', [ParticipantController::class, 'approve'])->name('peserta.approve');
        Route::get('/peserta/export/excel', [ParticipantController::class, 'exportExcel'])->name('peserta.export.excel');
        Route::get('/peserta/export/pdf', [ParticipantController::class, 'exportPdf'])->name('peserta.export.pdf');
        Route::post('/peserta/{participant}/device/reset', [ParticipantController::class, 'resetDevice'])->name('peserta.device.reset');

        // Pembimbing
        Route::get('/pembimbing/data', [SupervisorController::class, 'data'])->name('pembimbing.data');
        Route::resource('pembimbing', SupervisorController::class)
            ->names('pembimbing')
            ->parameters(['pembimbing' => 'supervisor']);
        Route::get('/pembimbing/export/excel', [SupervisorController::class, 'exportExcel'])->name('pembimbing.export.excel');
        Route::get('/pembimbing/export/pdf', [SupervisorController::class, 'exportPdf'])->name('pembimbing.export.pdf');

        // Instansi
        Route::get('/instansi/data', [InstituteController::class, 'data'])->name('instansi.data');
        Route::resource('instansi', InstituteController::class)
            ->names('instansi')
            ->parameters(['instansi' => 'institute']);
        Route::get('/instansi/export/excel', [InstituteController::class, 'exportExcel'])->name('instansi.export.excel');
        Route::get('/instansi/export/pdf', [InstituteController::class, 'exportPdf'])->name('instansi.export.pdf');

        // Magang / Internship
        Route::get('/internship/data', [InternshipController::class, 'data'])->name('internship.data');
        Route::resource('internship', InternshipController::class);
        Route::get('/internship/export/excel', [InternshipController::class, 'exportExcel'])->name('internship.export.excel');
        Route::get('/internship/export/pdf', [InternshipController::class, 'exportPdf'])->name('internship.export.pdf');
        Route::put('/internship/{internship}/update-status', [InternshipController::class, 'updateStatus'])->name('internship.update-status');

        // Penugasan / Tasks
        Route::get('/penugasan/data', [TaskController::class, 'data'])->name('penugasan.data');
        Route::resource('penugasan', TaskController::class)
            ->names('penugasan')
            ->parameters(['penugasan' => 'task']);

        // Pengguna
        Route::get('/users/data', [UsersController::class, 'data'])->name('users.data');
        Route::resource('users', UsersController::class)->names('users');
        Route::put('/users/{user}/status', [UsersController::class, 'toggleStatus'])->name('users.status');

        // QR Code Absensi
        Route::get('/attendance/qrcode', [AdminAttendanceController::class, 'showQrCode'])->name('attendance.qrcode');
        Route::get('/attendance/report/daily', [AdminAttendanceReportController::class, 'daily'])->name('attendance.daily');

        // -- Profil Pengguna (dari Breeze) --
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

        // -- Peserta --
        Route::get('/peserta/data', [ParticipantController::class, 'data'])->name('peserta.data');
        Route::resource('peserta', ParticipantController::class)->names('peserta')->parameters(['peserta' => 'participant']);
        Route::get('/peserta/{participant}/approve', [ParticipantController::class, 'approve'])->name('peserta.approve');
        Route::get('/peserta/export/excel', [ParticipantController::class, 'exportExcel'])->name('peserta.export.excel');
        Route::get('/peserta/export/pdf', [ParticipantController::class, 'exportPdf'])->name('peserta.export.pdf');

        // === NEW: export riwayat logbook & absensi per peserta ===
        Route::get('/peserta/{participant}/logbook/export/excel', [ParticipantController::class, 'exportLogbookExcel'])->name('peserta.logbook.export.excel');
        Route::get('/peserta/{participant}/logbook/export/pdf', [ParticipantController::class, 'exportLogbookPdf'])->name('peserta.logbook.export.pdf');
        Route::get('/peserta/{participant}/absen/export/excel', [ParticipantController::class, 'exportAttendanceExcel'])->name('peserta.absen.export.excel');
        Route::get('/peserta/{participant}/absen/export/pdf', [ParticipantController::class, 'exportAttendancePdf'])->name('peserta.absen.export.pdf');

        // ===== Absensi Manual (Admin) =====
        Route::get('/attendance/manual/create', [\App\Http\Controllers\Admin\ManualAttendanceController::class, 'create'])
            ->name('attendance.manual.create');
        Route::post('/attendance/manual', [\App\Http\Controllers\Admin\ManualAttendanceController::class, 'store'])
            ->name('attendance.manual.store');

        Route::resource('changelog', ChangelogController::class)->names('changelog');
    });

/*
|--------------------------------------------------------------------------
| PEMAGANG
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:pemagang'])
    ->prefix('pemagang')
    ->name('pemagang.')
    ->group(function () {

        Route::get('/dashboard', [PemagangDashboardController::class, 'index'])->name('dashboard.index');

        Route::get('/logbook/data', [LogbookController::class, 'data'])->name('logbook.data');
        Route::resource('logbook', LogbookController::class)->names('logbook');

        // Route::get('/attendance/record', [PemagangAttendanceController::class, 'record'])->name('attendance.record');
        Route::get('/attendance', [PemagangAttendanceController::class, 'index'])->name('attendance.index');

        Route::get('/attendance', [\App\Http\Controllers\Pemagang\AttendanceController::class, 'index'])->name('attendance.index');

        // -- Profil Pengguna (dari Breeze) --
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::resource('changelog', ChangelogController::class)->names('changelog');
        Route::post('/device/register', [DeviceController::class, 'register'])
            ->name('device.register');
        Route::post('/attendance/record/{type}', [PemagangAttendanceController::class, 'record'])
            ->name('attendance.record')
            ->where('type', 'check-in|check-out');
    });

/*
|--------------------------------------------------------------------------
| ATASAN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:atasan'])
    ->prefix('atasan')
    ->name('atasan.')
    ->group(function () {

        Route::get('/dashboard', [AtasanDashboardController::class, 'index'])->name('dashboard.index');

        // Permohonan
        Route::get('/permohonan/data', [AtasanPermohonanController::class, 'data'])->name('permohonan.data');
        Route::resource('permohonan', AtasanPermohonanController::class)
            ->names('permohonan')
            ->parameters(['permohonan' => 'application']);
        Route::patch('/permohonan/{application}/status', [AtasanPermohonanController::class, 'updateStatus'])->name('permohonan.status');
        Route::get('/permohonan/export/excel', [AtasanPermohonanController::class, 'exportExcel'])->name('permohonan.export.excel');
        Route::get('/permohonan/export/pdf', [AtasanPermohonanController::class, 'exportPdf'])->name('permohonan.export.pdf');

        // Peserta
        Route::get('/peserta/data', [AtasanParticipantController::class, 'data'])->name('peserta.data');
        Route::resource('peserta', AtasanParticipantController::class)
            ->names('peserta')
            ->parameters(['peserta' => 'participant']);
        Route::get('/peserta/export/excel', [AtasanParticipantController::class, 'exportExcel'])->name('peserta.export.excel');
        Route::get('/peserta/export/pdf', [AtasanParticipantController::class, 'exportPdf'])->name('peserta.export.pdf');

        // Pembimbing
        Route::get('/pembimbing/data', [AtasanSupervisorController::class, 'data'])->name('pembimbing.data');
        Route::resource('pembimbing', AtasanSupervisorController::class)
            ->names('pembimbing')
            ->parameters(['pembimbing' => 'supervisor']);
        Route::get('/pembimbing/export/excel', [AtasanSupervisorController::class, 'exportExcel'])->name('pembimbing.export.excel');
        Route::get('/pembimbing/export/pdf', [AtasanSupervisorController::class, 'exportPdf'])->name('pembimbing.export.pdf');

        // Instansi
        Route::get('/instansi/data', [AtasanInstituteController::class, 'data'])->name('instansi.data');
        Route::resource('instansi', AtasanInstituteController::class)
            ->names('instansi')
            ->parameters(['instansi' => 'institute']);
        Route::get('/instansi/export/excel', [AtasanInstituteController::class, 'exportExcel'])->name('instansi.export.excel');
        Route::get('/instansi/export/pdf', [AtasanInstituteController::class, 'exportPdf'])->name('instansi.export.pdf');

        // Magang / Internship
        Route::get('/internship/data', [AtasanInternshipController::class, 'data'])->name('internship.data');
        Route::resource('internship', AtasanInternshipController::class);
        Route::get('/internship/export/excel', [AtasanInternshipController::class, 'exportExcel'])->name('internship.export.excel');
        Route::get('/internship/export/pdf', [AtasanInternshipController::class, 'exportPdf'])->name('internship.export.pdf');

        // Penugasan / Tasks
        Route::get('/penugasan/data', [AtasanTaskController::class, 'data'])->name('penugasan.data');
        Route::resource('penugasan', AtasanTaskController::class)
            ->names('penugasan')
            ->parameters(['penugasan' => 'task']);

        Route::resource('changelog', ChangelogController::class)->names('changelog');

        // -- Profil Pengguna (dari Breeze) --
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    });

/*
|--------------------------------------------------------------------------
| PEMBIMBING
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:pembimbing'])
    ->prefix('pembimbing')
    ->name('pembimbing.')
    ->group(function () {
        Route::get('/dashboard', [PembimbingDashboardController::class, 'index'])
            ->name('dashboard.index');

        // Peserta
        Route::get('/peserta', [PembimbingParticipantController::class, 'index'])->name('peserta.index');
        Route::get('/peserta/{participant}', [PembimbingParticipantController::class, 'show'])->name('peserta.show');

        // Overview Penugasan (daftar peserta bimbingan + aksi)
        Route::get('/penugasan', [PembimbingTaskOverviewController::class, 'index'])
            ->name('task.participants');

        // Task per peserta
        Route::get('/peserta/{participant}/task', [PembimbingTaskController::class, 'index'])
            ->name('task.index');
        Route::get('/peserta/{participant}/task/create', [PembimbingTaskController::class, 'create'])
            ->name('task.create');
        Route::post('/peserta/{participant}/task', [PembimbingTaskController::class, 'store'])
            ->name('task.store');

        // === Tambahkan ini (EDIT/UPDATE/DELETE) ===
        Route::get('/peserta/{participant}/task/{task}/edit', [PembimbingTaskController::class, 'edit'])
            ->name('task.edit');
        Route::put('/peserta/{participant}/task/{task}', [PembimbingTaskController::class, 'update'])
            ->name('task.update');
        Route::delete('/peserta/{participant}/task/{task}', [PembimbingTaskController::class, 'destroy'])
            ->name('task.destroy');

        Route::resource('changelog', ChangelogController::class)->names('changelog');
    });

// Route autentikasi Breeze
require __DIR__ . '/auth.php';
