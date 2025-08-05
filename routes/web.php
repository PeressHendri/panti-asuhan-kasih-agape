<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Pengasuh\PengasuhController;
use App\Http\Controllers\Donatur\DonaturController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ChildController;
use Illuminate\Support\Facades\Route;

// Route utama untuk landing page
Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/profil-panti', [PublicController::class, 'profile'])->name('public.profile');

// Route untuk assets static
Route::get('/assets/{path}', function($path) {
    $filePath = public_path("assets/$path");
    if (file_exists($filePath)) {
        return response()->file($filePath);
    }
    abort(404);
})->where('path', '.*');

// Route untuk build assets
Route::get('/build/{path}', function($path) {
    $filePath = public_path("build/$path");
    if (file_exists($filePath)) {
        return response()->file($filePath);
    }
    abort(404);
})->where('path', '.*');

// Route untuk favicon
Route::get('/favicon.ico', function() {
    return response()->file(public_path('favicon.ico'));
});

// Route lainnya (akan di-disable sementara untuk Vercel)
if (env('APP_ENV') !== 'production' || env('ENABLE_FULL_APP', false)) {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

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
        

        Route::get('/cctv', [AdminController::class, 'cctv'])->name('cctv');
        

        Route::get('/kehadiran', [AdminController::class, 'attendance'])->name('attendance');
        Route::post('/kehadiran/check-in', [AdminController::class, 'checkIn'])->name('attendance.check-in');
        Route::post('/kehadiran/check-out/{id}', [AdminController::class, 'checkOut'])->name('attendance.check-out');
        Route::post('/kehadiran/manual', [AdminController::class, 'manualAttendance'])->name('attendance.manual');
        Route::put('/kehadiran/update', [AdminController::class, 'updateAttendance'])->name('attendance.update');
    });


    Route::middleware(['auth', 'role:pengasuh'])->prefix('pengasuh')->name('pengasuh.')->group(function () {
      
        Route::get('/dashboard', [PengasuhController::class, 'dashboard'])->name('dashboard');
        
        
        Route::get('/profil-panti', [PengasuhController::class, 'profilePanti'])->name('profile.panti');
        Route::get('/profil-panti/create', [PengasuhController::class, 'create'])->name('profile.panti.create');
        Route::post('/profil-panti', [PengasuhController::class, 'store'])->name('profile.panti.store');
        Route::get('/profil-panti/{id}/edit', [PengasuhController::class, 'edit'])->name('profile.panti.edit');
        Route::put('/profil-panti/{id}', [PengasuhController::class, 'update'])->name('profile.panti.update');
        Route::delete('/profil-panti/{id}', [PengasuhController::class, 'destroy'])->name('profile.panti.destroy');    
        
        Route::get('/cctv', [PengasuhController::class, 'cctv'])->name('cctv');
        
        Route::get('/kehadiran', [PengasuhController::class, 'attendance'])->name('attendance');
        Route::post('/kehadiran/check-in', [PengasuhController::class, 'checkIn'])->name('attendance.check-in');
        Route::post('/kehadiran/check-out/{id}', [PengasuhController::class, 'checkOut'])->name('attendance.check-out');
        Route::post('/kehadiran/manual', [PengasuhController::class, 'manualAttendance'])->name('attendance.manual');
        Route::put('/kehadiran/update', [PengasuhController::class, 'updateAttendance'])->name('attendance.update');
    });


    Route::middleware(['auth', 'role:donatur'])->prefix('donatur')->name('donatur.')->group(function () {
        Route::get('/dashboard', [DonaturController::class, 'dashboard'])->name('dashboard');
        Route::get('/profil-panti', [DonaturController::class, 'profilePanti'])->name('profile.panti');
        Route::get('/cctv', [DonaturController::class, 'cctv'])->name('cctv');
        Route::get('/kehadiran', [DonaturController::class, 'attendance'])->name('attendance');
    });

    Route::get('donatur/pengasuh', [DonaturController::class, 'pengasuh'])->name('donatur.pengasuh');

    Route::middleware(['auth', 'role:pengasuh,donatur'])->group(function () {
        Route::get('/profil-saya', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
        Route::post('/profil-saya', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    });

    Route::middleware(['auth'])->group(function () {
        Route::get('/children', [ChildController::class, 'index'])->name('children.index');
        Route::get('/children/{id}', [ChildController::class, 'show'])->name('children.show');
    });

    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/admin/profile/edit', [\App\Http\Controllers\Admin\AdminController::class, 'editProfile'])->name('admin.profile.edit');
        Route::post('/admin/profile/update', [\App\Http\Controllers\Admin\AdminController::class, 'updateProfile'])->name('admin.profile.update');
    });
}