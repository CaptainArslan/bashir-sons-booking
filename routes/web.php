<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;

Route::get('/', function () {
    // return redirect()->intended(route('dashboard', absolute: false));
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['guest', '2fa.pending'])->group(function () {
    Route::get('/two-factor-challenge', [TwoFactorController::class, 'challenge'])->name('2fa.challenge');
    Route::post('/two-factor-challenge', [TwoFactorController::class, 'verifyChallenge'])->name('2fa.verify');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/user/two-factor', [TwoFactorController::class, 'show'])->name('2fa.show');
    Route::post('/user/two-factor/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::post('/user/two-factor/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable');

    Route::middleware(['role:super_admin|admin'])->group(function () {
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    });
});

require __DIR__ . '/auth.php';
