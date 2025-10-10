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
});

require __DIR__ . '/auth.php';



// Route::middleware(['auth', 'role:admin'])->group(function () {
//     Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
// });

// Route::middleware(['auth', 'role:employee'])->group(function () {
//     Route::get('/employee/dashboard', [EmployeeController::class, 'index'])->name('employee.dashboard');
// });

// Route::middleware(['auth', 'role:customer'])->group(function () {
//     Route::get('/customer/dashboard', [CustomerController::class, 'index'])->name('customer.dashboard');
// });
