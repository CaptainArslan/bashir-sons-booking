<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TwoFactorController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/two-factor-challenge', [TwoFactorController::class, 'challenge'])
        ->middleware('2fa.pending')
        ->name('2fa.challenge');

    Route::post('/two-factor-challenge', [TwoFactorController::class, 'verifyChallenge'])
        ->middleware('2fa.pending')
        ->name('2fa.verify');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/user/two-factor', [TwoFactorController::class, 'show'])->name('2fa.show');
    Route::post('/user/two-factor/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::post('/user/two-factor/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable');
});

require __DIR__ . '/auth.php';
