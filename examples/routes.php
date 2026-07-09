<?php

use App\Http\Controllers\LeadController;
use Illuminate\Support\Facades\Route;

/**
 * Example route group protected by laravel-bitwise-permission.
 *
 * See: https://bitwise.tchenry.com/docs/middleware
 */
Route::middleware(['auth', 'bwp.permission'])->group(function () {
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
});

Route::middleware(['auth', 'bwp.permission:create'])->group(function () {
    Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
});

Route::middleware(['auth', 'bwp.permission:update'])->group(function () {
    Route::get('/leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
    Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
});

Route::middleware(['auth', 'bwp.permission:delete'])->group(function () {
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
});
