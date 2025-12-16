<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BarangController;

Route::prefix('barang')->group(function () {
    Route::get('/', [BarangController::class, 'index']);
    Route::post('/', [BarangController::class, 'store']);
    Route::get('/{id}', [BarangController::class, 'show']);
    Route::put('/{id}', [BarangController::class, 'update']);
});
