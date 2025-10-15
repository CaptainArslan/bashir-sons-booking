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
use App\Http\Controllers\Admin\BusController;
use App\Http\Controllers\Admin\BusTypeController;
use App\Http\Controllers\Admin\BusLayoutController;
use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\GeneralSettingController;
use App\Http\Controllers\Admin\EnquiryController;

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

        // Bus Types Routes
        Route::get('/bus-types', [BusTypeController::class, 'index'])->name('bus-types.index');
        Route::get('/bus-types/data', [BusTypeController::class, 'getData'])->name('bus-types.data');
        Route::get('/bus-types/create', [BusTypeController::class, 'create'])->name('bus-types.create');
        Route::post('/bus-types', [BusTypeController::class, 'store'])->name('bus-types.store');
        Route::get('/bus-types/{id}/edit', [BusTypeController::class, 'edit'])->name('bus-types.edit');
        Route::put('/bus-types/{id}', [BusTypeController::class, 'update'])->name('bus-types.update');
        Route::delete('/bus-types/{id}', [BusTypeController::class, 'destroy'])->name('bus-types.destroy');

        // Bus Layouts Routes
        Route::get('/bus-layouts', [BusLayoutController::class, 'index'])->name('bus-layouts.index');
        Route::get('/bus-layouts/data', [BusLayoutController::class, 'getData'])->name('bus-layouts.data');
        Route::get('/bus-layouts/create', [BusLayoutController::class, 'create'])->name('bus-layouts.create');
        Route::post('/bus-layouts', [BusLayoutController::class, 'store'])->name('bus-layouts.store');
        Route::get('/bus-layouts/{id}/edit', [BusLayoutController::class, 'edit'])->name('bus-layouts.edit');
        Route::put('/bus-layouts/{id}', [BusLayoutController::class, 'update'])->name('bus-layouts.update');
        Route::delete('/bus-layouts/{id}', [BusLayoutController::class, 'destroy'])->name('bus-layouts.destroy');

        // Facilities Routes
        Route::get('/facilities', [FacilityController::class, 'index'])->name('facilities.index');
        Route::get('/facilities/data', [FacilityController::class, 'getData'])->name('facilities.data');
        Route::get('/facilities/create', [FacilityController::class, 'create'])->name('facilities.create');
        Route::post('/facilities', [FacilityController::class, 'store'])->name('facilities.store');
        Route::get('/facilities/{id}/edit', [FacilityController::class, 'edit'])->name('facilities.edit');
        Route::put('/facilities/{id}', [FacilityController::class, 'update'])->name('facilities.update');
        Route::delete('/facilities/{id}', [FacilityController::class, 'destroy'])->name('facilities.destroy');

        // Buses Routes
        Route::get('/buses', [BusController::class, 'index'])->name('buses.index');
        Route::get('/buses/data', [BusController::class, 'getData'])->name('buses.data');
        Route::get('/buses/create', [BusController::class, 'create'])->name('buses.create');
        Route::post('/buses', [BusController::class, 'store'])->name('buses.store');
        Route::get('/buses/{id}/edit', [BusController::class, 'edit'])->name('buses.edit');
        Route::put('/buses/{id}', [BusController::class, 'update'])->name('buses.update');
        Route::delete('/buses/{id}', [BusController::class, 'destroy'])->name('buses.destroy');

        // Banners Routes
        Route::get('/banners', [BannerController::class, 'index'])->name('banners.index');
        Route::get('/banners/data', [BannerController::class, 'getData'])->name('banners.data');
        Route::get('/banners/create', [BannerController::class, 'create'])->name('banners.create');
        Route::post('/banners', [BannerController::class, 'store'])->name('banners.store');
        Route::get('/banners/{id}/edit', [BannerController::class, 'edit'])->name('banners.edit');
        Route::put('/banners/{id}', [BannerController::class, 'update'])->name('banners.update');
        Route::delete('/banners/{id}', [BannerController::class, 'destroy'])->name('banners.destroy');

        // General Settings Routes
        Route::get('/general-settings', [GeneralSettingController::class, 'index'])->name('general-settings.index');
        Route::get('/general-settings/create', [GeneralSettingController::class, 'create'])->name('general-settings.create');
        Route::post('/general-settings', [GeneralSettingController::class, 'store'])->name('general-settings.store');
        Route::get('/general-settings/{id}/edit', [GeneralSettingController::class, 'edit'])->name('general-settings.edit');
        Route::put('/general-settings/{id}', [GeneralSettingController::class, 'update'])->name('general-settings.update');
        Route::delete('/general-settings/{id}', [GeneralSettingController::class, 'destroy'])->name('general-settings.destroy');

        // Enquiries Routes
        Route::get('/enquiries', [EnquiryController::class, 'index'])->name('enquiries.index');
        Route::get('/enquiries/data', [EnquiryController::class, 'getData'])->name('enquiries.data');
        Route::get('/enquiries/{id}', [EnquiryController::class, 'show'])->name('enquiries.show');
        Route::delete('/enquiries/{id}', [EnquiryController::class, 'destroy'])->name('enquiries.destroy');

    });
});

require __DIR__ . '/auth.php';
