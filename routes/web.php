<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\RentalController;
use Illuminate\Support\Facades\Route;

Route::resource('categories', CategoryController::class)->except(['show']);
Route::resource('items', ItemController::class);
Route::resource('customers', CustomerController::class);
Route::resource('rentals', RentalController::class)->only(['index', 'create', 'store', 'show']);
Route::get('rentals/{id}/kembalikan', [RentalController::class, 'formKembali'])->name('rentals.formKembali');
Route::put('rentals/{id}/kembalikan', [RentalController::class, 'kembalikan'])->name('rentals.kembalikan');
Route::get('/laporan/kerusakan', [RentalController::class, 'laporanKerusakan'])->name('laporan.kerusakan');