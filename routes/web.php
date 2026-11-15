<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'index')->name('home');

Route::post('/lista-de-espera', [\App\Http\Controllers\WaitlistController::class, 'store'])
    ->name('waitlist.store')->middleware('throttle:5,1');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
