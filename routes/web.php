<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\RentalController;
use Illuminate\Support\Facades\Route;

// Publik (belum login)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Harus login
Route::middleware(['auth'])->group(function () {

    Route::get('/', fn () => redirect()->route('rentals.index'));

    // Khusus admin: kategori + tambah/ubah/hapus barang
    Route::middleware(['admin'])->group(function () {
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('items', ItemController::class)->except(['index', 'show']);
    });

    // Admin dan petugas: lihat barang (HARUS setelah group admin)
    Route::resource('items', ItemController::class)->only(['index', 'show']);

    // Admin dan petugas: pelanggan, sewa, pengembalian, laporan
    Route::resource('customers', CustomerController::class);

    Route::get('/rentals/{id}/kembalikan', [RentalController::class, 'formKembali'])->name('rentals.formKembali');
    Route::put('/rentals/{id}/kembalikan', [RentalController::class, 'kembalikan'])->name('rentals.kembalikan');
    Route::resource('rentals', RentalController::class)->only(['index', 'create', 'store', 'show']);

    Route::get('/laporan/kerusakan', [RentalController::class, 'laporanKerusakan'])->name('laporan.kerusakan');
});