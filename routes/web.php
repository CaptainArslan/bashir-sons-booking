<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\Admin\Citycontroller;
use App\Http\Controllers\Admin\Rolecontroller;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CounterTerminalController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;

// use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;

Route::get('/', function () {
    return redirect()->intended(route('dashboard', absolute: false));
    // return view('welcome');
});

Route::get('/home', [DashboardController::class, 'home'])->name('home');
Route::get('/services', [DashboardController::class, 'services'])->name('services');
Route::get('/bookings', [DashboardController::class, 'bookings'])->name('bookings');
Route::get('/about-us', [DashboardController::class, 'aboutUs'])->name('about-us');
Route::get('/contact', [DashboardController::class, 'contact'])->name('contact');
Route::post('/enquiry', [DashboardController::class, 'submitEnquiry'])->name('enquiry.submit');
Route::get('/booking', [DashboardController::class, 'booking'])->name('booking');

// Frontend Routes

Route::middleware(['guest', '2fa.pending'])->group(function () {
    Route::get('/two-factor-challenge', [TwoFactorController::class, 'challenge'])->name('2fa.challenge');
    Route::post('/two-factor-challenge', [TwoFactorController::class, 'verifyChallenge'])->name('2fa.verify');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/user/two-factor', [TwoFactorController::class, 'show'])->name('2fa.show');
    Route::post('/user/two-factor/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::post('/user/two-factor/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Roles Routes
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/data', [RoleController::class, 'getData'])->name('roles.data');
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{id}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{id}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');

        // Cities Routes
        Route::get('/cities', [CityController::class, 'index'])->name('cities.index');
        Route::get('/cities/data', [CityController::class, 'getData'])->name('cities.data');
        Route::get('/cities/create', [CityController::class, 'create'])->name('cities.create');
        Route::post('/cities', [CityController::class, 'store'])->name('cities.store');
        Route::get('/cities/{id}/edit', [CityController::class, 'edit'])->name('cities.edit');
        Route::put('/cities/{id}', [CityController::class, 'update'])->name('cities.update');
        Route::delete('/cities/{id}', [CityController::class, 'destroy'])->name('cities.destroy');

        // Counter/Terminal Routes
        Route::get('/counter-terminals', [CounterTerminalController::class, 'index'])->name('counter-terminals.index');
        Route::get('/counter-terminals/data', [CounterTerminalController::class, 'getData'])->name('counter-terminals.data');
        Route::get('/counter-terminals/create', [CounterTerminalController::class, 'create'])->name('counter-terminals.create');
        Route::post('/counter-terminals', [CounterTerminalController::class, 'store'])->name('counter-terminals.store');
        Route::get('/counter-terminals/{id}/edit', [CounterTerminalController::class, 'edit'])->name('counter-terminals.edit');
        Route::put('/counter-terminals/{id}', [CounterTerminalController::class, 'update'])->name('counter-terminals.update');
        Route::delete('/counter-terminals/{id}', [CounterTerminalController::class, 'destroy'])->name('counter-terminals.destroy');

        // users Routes
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/data', [UserController::class, 'getData'])->name('users.data');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    });
});

require __DIR__ . '/auth.php';
