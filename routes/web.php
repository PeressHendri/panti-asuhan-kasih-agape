<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Pengasuh\PengasuhController;
use App\Http\Controllers\Donatur\DonaturController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ChildController;
use App\Http\Controllers\CctvController;
use App\Http\Controllers\FaceRecognitionController;
use Illuminate\Support\Facades\Route;

// Route utama untuk landing page
Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/profil-panti', [PublicController::class, 'profile'])->name('public.profile');

Route::get('/donasi', [PublicController::class, 'donasi'])->name('public.donasi');
Route::post('/donasi', [PublicController::class, 'storeDonasi'])->name('public.donasi.store');

// Route untuk assets static
Route::get('/assets/{path}', function ($path) {
    $filePath = public_path("assets/$path");
    if (file_exists($filePath)) {
        return response()->file($filePath);
    }
    abort(404);
})->where('path', '.*');

// Route untuk build assets
Route::get('/build/{path}', function ($path) {
    $filePath = public_path("build/$path");
    if (file_exists($filePath)) {
        return response()->file($filePath);
    }
    abort(404);
})->where('path', '.*');

// Route untuk favicon
Route::get('/favicon.ico', function () {
    return response()->file(public_path('favicon.ico'));
});

// Route lainnya
Route::group([], function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

    // Lupa Password
    Route::get('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showForm'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showResetForm'])->name('password.reset.form');
    Route::post('/reset-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'resetPassword'])->name('password.reset');

    Route::middleware(['auth', 'role:admin,pengasuh'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        Route::get('/profil-panti', [AdminController::class, 'profilePanti'])->name('profile.panti');
        Route::get('/profil-panti/create', [AdminController::class, 'create'])->name('profile.panti.create');
        Route::post('/profil-panti', [AdminController::class, 'store'])->name('profile.panti.store');
        Route::get('/profil-panti/{id}/edit', [AdminController::class, 'edit'])->name('profile.panti.edit');
        Route::put('/profil-panti/{id}', [AdminController::class, 'update'])->name('profile.panti.update');
        Route::delete('/profil-panti/{id}', [AdminController::class, 'destroy'])->name('profile.panti.destroy');


        Route::get('/manajemen-pengguna', [AdminController::class, 'manageUsers'])->name('manage.users');
        Route::resource('users', AdminUserController::class)->except(['show']);





        Route::get('/kehadiran', [AdminController::class, 'attendance'])->name('attendance');
        Route::post('/kehadiran/check-in', [AdminController::class, 'checkIn'])->name('attendance.check-in');
        Route::post('/kehadiran/check-out/{id}', [AdminController::class, 'checkOut'])->name('attendance.check-out');
        Route::post('/kehadiran/manual', [AdminController::class, 'manualAttendance'])->name('attendance.manual');
        Route::put('/kehadiran/update', [AdminController::class, 'updateAttendance'])->name('attendance.update');
        Route::get('/kehadiran/export', [AdminController::class, 'exportAttendance'])->name('attendance.export');

        Route::resource('gallery', \App\Http\Controllers\GalleryController::class);

        // Donasi Management (Viewable by admin & pengasuh)
        Route::get('/donasi', [AdminController::class, 'donasiIndex'])->name('donasi');
    });


    Route::middleware(['auth', 'role:pengasuh'])->prefix('pengasuh')->name('pengasuh.')->group(function () {

        Route::get('/dashboard', [PengasuhController::class, 'dashboard'])->name('dashboard');


        Route::get('/profil-panti', [PengasuhController::class, 'profilePanti'])->name('profile.panti');
        Route::get('/profil-panti/create', [PengasuhController::class, 'create'])->name('profile.panti.create');
        Route::post('/profil-panti', [PengasuhController::class, 'store'])->name('profile.panti.store');
        Route::get('/profil-panti/{id}/edit', [PengasuhController::class, 'edit'])->name('profile.panti.edit');
        Route::put('/profil-panti/{id}', [PengasuhController::class, 'update'])->name('profile.panti.update');
        Route::delete('/profil-panti/{id}', [PengasuhController::class, 'destroy'])->name('profile.panti.destroy');



        Route::get('/kehadiran', [PengasuhController::class, 'attendance'])->name('attendance');
        Route::post('/kehadiran/check-in', [PengasuhController::class, 'checkIn'])->name('attendance.check-in');
        Route::post('/kehadiran/check-out/{id}', [PengasuhController::class, 'checkOut'])->name('attendance.check-out');
        Route::post('/kehadiran/manual', [PengasuhController::class, 'manualAttendance'])->name('attendance.manual');
        Route::put('/kehadiran/update', [PengasuhController::class, 'updateAttendance'])->name('attendance.update');

        Route::resource('gallery', \App\Http\Controllers\GalleryController::class);
    });


    Route::middleware(['auth', 'role:sponsor'])->prefix('sponsor')->name('sponsor.')->group(function () {
        Route::get('/dashboard', [DonaturController::class, 'dashboard'])->name('dashboard');
        Route::get('/profil-panti', [DonaturController::class, 'profilePanti'])->name('profile.panti');

        Route::get('/kehadiran', [DonaturController::class, 'attendance'])->name('attendance');
    });

    Route::get('sponsor/pengasuh', [DonaturController::class, 'pengasuh'])->name('sponsor.pengasuh')->middleware('auth');

    Route::middleware(['auth', 'role:pengasuh,sponsor,admin'])->group(function () {
        Route::get('/profil-saya', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
        Route::post('/profil-saya', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    });

    Route::middleware(['auth'])->group(function () {
        Route::get('/children', [ChildController::class, 'index'])->name('children.index');
        Route::get('/children/{id}', [ChildController::class, 'show'])->name('children.show');

        // CCTV Monitoring & Face Recognition (Unified)
        Route::get('/dashboard/cctv', [CctvController::class, 'index'])->name('dashboard.cctv');
        Route::get('/dashboard/cctv/live-data', [CctvController::class, 'liveData'])->name('dashboard.cctv.live');
        Route::post('/dashboard/cctv/{id}/refresh', [CctvController::class, 'refresh'])->name('dashboard.cctv.refresh');

        Route::get('/dashboard/face-log', [FaceRecognitionController::class, 'dashboard'])->name('dashboard.face-log');
    });

    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/admin/profile/edit', [AdminController::class, 'editProfile'])->name('admin.profile.edit');
        Route::post('/admin/profile/update', [AdminController::class, 'updateProfile'])->name('admin.profile.update');
        Route::get('/admin/settings', [AdminController::class, 'getSettings'])->name('admin.settings');

        // Admin CRUD CCTV
        Route::post('/dashboard/cctv', [CctvController::class, 'store'])->name('admin.cctv.store');
        Route::put('/dashboard/cctv/{id}', [CctvController::class, 'update'])->name('admin.cctv.update');
        Route::delete('/dashboard/cctv/{id}', [CctvController::class, 'destroy'])->name('admin.cctv.destroy');

        Route::delete('/admin/kehadiran/{id}', [AdminController::class, 'deleteAttendance'])->name('admin.attendance.delete');

        // Donasi Management (Action only for admin)
        Route::post('/admin/donasi/{id}/verify', [AdminController::class, 'donasiVerify'])->name('admin.donasi.verify');

        // Sinkronisasi label_map
        Route::get('/admin/sync-label-map', [AdminController::class, 'syncLabelMap'])->name('admin.sync.label');

        // CI/CD Deployment Route
        Route::post('/admin/deploy', [AdminController::class, 'deploy'])->name('admin.deploy');
    });
});