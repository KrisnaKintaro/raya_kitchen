<?php

use App\Http\Controllers\Api\Auth\LogInOutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Pembeli\KatalogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// ===========================
// ROUTE AUTH
// ===========================
Route::prefix('auth')->middleware('web')->group(function () {
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/verify-otp', [RegisterController::class, 'verifyOtp']);
    Route::post('/login', [LogInOutController::class, 'login']);
    Route::get('/logout',[LogInOutController::class, 'logout'])->middleware('auth:sanctum');
});

// ========================================================================================
// ROUTE TAMPILAN PEMBELI
// Semua api route atau api gateway masukkan ke prefix pembeli
// jadi ntar pake nya atau manggilnya api/pembeli/nama api nya
// ========================================================================================
Route::prefix('pembeli')->group(function () {
    Route::get('/katalog', [KatalogController::class, 'index']);
    Route::get('/katalog/{id}', [KatalogController::class, 'show']);
});


// ========================================================================================
// ROUTE TAMPILAN ADMIN
// Semua api route atau api gateway masukkan ke prefix admin
// jadi ntar pake nya atau manggilnya api/admin/nama api nya
// ========================================================================================
Route::prefix('admin')->group(function () {
    // Tambahkan route admin di sini
});
