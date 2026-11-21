<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;

Route::resource('categories', CategoryController::class)->except(['show']);
Route::resource('items', ItemController::class);
Route::resource('customers', CustomerController::class);
