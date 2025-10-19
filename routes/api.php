<?php

use App\Http\Controllers\PackageController;
use Illuminate\Support\Facades\Route;

Route::prefix('packages')->group(function (): void {
    Route::post('/import', [PackageController::class, 'import'])->name('packages.import');
    Route::get('/', [PackageController::class, 'index'])->name('packages.index');
    Route::patch('/{packageId}/status', [PackageController::class, 'update'])->name('packages.update-status');
    Route::delete('/{packageId}', [PackageController::class, 'destroy'])->name('packages.destroy');
});
