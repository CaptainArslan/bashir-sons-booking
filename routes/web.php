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
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RouteController;
use App\Http\Controllers\Admin\RouteStopController;
use App\Http\Controllers\Admin\RouteFareController;
use App\Http\Controllers\Admin\RouteTimetableController;
use App\Http\Controllers\Admin\RouteStopTimeController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Customer\BookingController;

// use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;

Route::get('/', function () {
    return redirect()->route('home');
    // return redirect()->intended(route('dashboard', absolute: false));
    // return view('welcome');
});

Route::get('/home', [DashboardController::class, 'home'])->name('home');
Route::get('/services', [DashboardController::class, 'services'])->name('services');
Route::get('/bookings', [DashboardController::class, 'bookings'])->name('bookings');
Route::get('/about-us', [DashboardController::class, 'aboutUs'])->name('about-us');
Route::get('/contact', [DashboardController::class, 'contact'])->name('contact');
Route::post('/enquiry', [DashboardController::class, 'submitEnquiry'])->name('enquiry.submit');

// Customer Routes
Route::get('/booking', [BookingController::class, 'index'])->name('customer.booking.index');
Route::get('/booking/search', [BookingController::class, 'search'])->name('customer.booking.search');
Route::get('/booking/{route}', [BookingController::class, 'show'])->name('customer.booking.show');
Route::post('/booking', [BookingController::class, 'store'])->name('customer.booking.store');

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

    Route::prefix('admin')->name('admin.')->middleware(['can:access admin panel'])->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Roles Routes
        Route::get('/roles', [RoleController::class, 'index'])->can('view roles')->name('roles.index');
        Route::get('/roles/data', [RoleController::class, 'getData'])->can('view roles')->name('roles.data');
        Route::get('/roles/create', [RoleController::class, 'create'])->can('create roles')->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->can('create roles')->name('roles.store');
        Route::get('/roles/{id}/edit', [RoleController::class, 'edit'])->can('edit roles')->name('roles.edit');
        Route::put('/roles/{id}', [RoleController::class, 'update'])->can('edit roles')->name('roles.update');
        Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->can('delete roles')->name('roles.destroy');

        // Permissions Routes
        Route::get('/permissions', [PermissionController::class, 'index'])->can('view permissions')->name('permissions.index');
        Route::get('/permissions/data', [PermissionController::class, 'getData'])->can('view permissions')->name('permissions.data');
        // Route::get('/permissions/create', [PermissionController::class, 'create'])->can('create permissions')->name('permissions.create');
        // Route::post('/permissions', [PermissionController::class, 'store'])->can('create permissions')->name('permissions.store');
        // Route::get('/permissions/{id}/edit', [PermissionController::class, 'edit'])->can('edit permissions')->name('permissions.edit');
        // Route::put('/permissions/{id}', [PermissionController::class, 'update'])->can('edit permissions')->name('permissions.update');
        // Route::delete('/permissions/{id}', [PermissionController::class, 'destroy'])->can('delete permissions')->name('permissions.destroy');

        // Cities Routes
        Route::get('/cities', [CityController::class, 'index'])->can('view cities')->name('cities.index');
        Route::get('/cities/data', [CityController::class, 'getData'])->can('view cities')->name('cities.data');
        Route::get('/cities/create', [CityController::class, 'create'])->can('create cities')->name('cities.create');
        Route::post('/cities', [CityController::class, 'store'])->can('create cities')->name('cities.store');
        Route::get('/cities/{id}/edit', [CityController::class, 'edit'])->can('edit cities')->name('cities.edit');
        Route::put('/cities/{id}', [CityController::class, 'update'])->can('edit cities')->name('cities.update');
        Route::delete('/cities/{id}', [CityController::class, 'destroy'])->can('delete cities')->name('cities.destroy');

        // Counter/Terminal Routes
        Route::get('/counter-terminals', [CounterTerminalController::class, 'index'])->can('view terminals')->name('counter-terminals.index');
        Route::get('/counter-terminals/data', [CounterTerminalController::class, 'getData'])->can('view terminals')->name('counter-terminals.data');
        Route::get('/counter-terminals/create', [CounterTerminalController::class, 'create'])->can('create terminals')->name('counter-terminals.create');
        Route::post('/counter-terminals', [CounterTerminalController::class, 'store'])->can('create terminals')->name('counter-terminals.store');
        Route::get('/counter-terminals/{id}/edit', [CounterTerminalController::class, 'edit'])->can('edit terminals')->name('counter-terminals.edit');
        Route::put('/counter-terminals/{id}', [CounterTerminalController::class, 'update'])->can('edit terminals')->name('counter-terminals.update');
        Route::delete('/counter-terminals/{id}', [CounterTerminalController::class, 'destroy'])->can('delete terminals')->name('counter-terminals.destroy');

        // Users Routes
        Route::get('/users', [UserController::class, 'index'])->can('view users')->name('users.index');
        Route::get('/users/data', [UserController::class, 'getData'])->can('view users')->name('users.data');
        Route::get('/users/create', [UserController::class, 'create'])->can('create users')->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->can('create users')->name('users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->can('edit users')->name('users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])->can('edit users')->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->can('delete users')->name('users.destroy');

        // Employees Routes
        Route::get('/employees', [EmployeeController::class, 'index'])->can('manage users')->name('employees.index');
        Route::get('/employees/data', [EmployeeController::class, 'getData'])->can('manage users')->name('employees.data');
        Route::get('/employees/stats', [EmployeeController::class, 'stats'])->can('manage users')->name('employees.stats');
        Route::get('/employees/create', [EmployeeController::class, 'create'])->can('manage users')->name('employees.create');
        Route::post('/employees', [EmployeeController::class, 'store'])->can('manage users')->name('employees.store');
        Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->can('manage users')->name('employees.destroy');

        // Bus Types Routes
        Route::get('/bus-types', [BusTypeController::class, 'index'])->can('view bus types')->name('bus-types.index');
        Route::get('/bus-types/data', [BusTypeController::class, 'getData'])->can('view bus types')->name('bus-types.data');
        Route::get('/bus-types/create', [BusTypeController::class, 'create'])->can('create bus types')->name('bus-types.create');
        Route::post('/bus-types', [BusTypeController::class, 'store'])->can('create bus types')->name('bus-types.store');
        Route::get('/bus-types/{id}/edit', [BusTypeController::class, 'edit'])->can('edit bus types')->name('bus-types.edit');
        Route::put('/bus-types/{id}', [BusTypeController::class, 'update'])->can('edit bus types')->name('bus-types.update');
        Route::delete('/bus-types/{id}', [BusTypeController::class, 'destroy'])->can('delete bus types')->name('bus-types.destroy');

        // Bus Layouts Routes
        Route::get('/bus-layouts', [BusLayoutController::class, 'index'])->can('view bus layouts')->name('bus-layouts.index');
        Route::get('/bus-layouts/data', [BusLayoutController::class, 'getData'])->can('view bus layouts')->name('bus-layouts.data');
        Route::get('/bus-layouts/create', [BusLayoutController::class, 'create'])->can('create bus layouts')->name('bus-layouts.create');
        Route::post('/bus-layouts', [BusLayoutController::class, 'store'])->can('create bus layouts')->name('bus-layouts.store');
        Route::get('/bus-layouts/{id}/edit', [BusLayoutController::class, 'edit'])->can('edit bus layouts')->name('bus-layouts.edit');
        Route::put('/bus-layouts/{id}', [BusLayoutController::class, 'update'])->can('edit bus layouts')->name('bus-layouts.update');
        Route::delete('/bus-layouts/{id}', [BusLayoutController::class, 'destroy'])->can('delete bus layouts')->name('bus-layouts.destroy');
        
        // Seat map specific routes
        Route::post('/bus-layouts/generate-seat-map', [BusLayoutController::class, 'generateSeatMap'])->can('create bus layouts')->name('bus-layouts.generate-seat-map');
        Route::post('/bus-layouts/{id}/update-seat', [BusLayoutController::class, 'updateSeat'])->can('edit bus layouts')->name('bus-layouts.update-seat');

        // Facilities Routes
        Route::get('/facilities', [FacilityController::class, 'index'])->can('view facilities')->name('facilities.index');
        Route::get('/facilities/data', [FacilityController::class, 'getData'])->can('view facilities')->name('facilities.data');
        Route::get('/facilities/create', [FacilityController::class, 'create'])->can('create facilities')->name('facilities.create');
        Route::post('/facilities', [FacilityController::class, 'store'])->can('create facilities')->name('facilities.store');
        Route::get('/facilities/{id}/edit', [FacilityController::class, 'edit'])->can('edit facilities')->name('facilities.edit');
        Route::put('/facilities/{id}', [FacilityController::class, 'update'])->can('edit facilities')->name('facilities.update');
        Route::delete('/facilities/{id}', [FacilityController::class, 'destroy'])->can('delete facilities')->name('facilities.destroy');

        // Buses Routes
        Route::get('/buses', [BusController::class, 'index'])->can('view buses')->name('buses.index');
        Route::get('/buses/data', [BusController::class, 'getData'])->can('view buses')->name('buses.data');
        Route::get('/buses/create', [BusController::class, 'create'])->can('create buses')->name('buses.create');
        Route::post('/buses', [BusController::class, 'store'])->can('create buses')->name('buses.store');
        Route::get('/buses/{id}/edit', [BusController::class, 'edit'])->can('edit buses')->name('buses.edit');
        Route::put('/buses/{id}', [BusController::class, 'update'])->can('edit buses')->name('buses.update');
        Route::delete('/buses/{id}', [BusController::class, 'destroy'])->can('delete buses')->name('buses.destroy');

        // Banners Routes
        Route::get('/banners', [BannerController::class, 'index'])->can('view banners')->name('banners.index');
        Route::get('/banners/data', [BannerController::class, 'getData'])->can('view banners')->name('banners.data');
        Route::get('/banners/create', [BannerController::class, 'create'])->can('create banners')->name('banners.create');
        Route::post('/banners', [BannerController::class, 'store'])->can('create banners')->name('banners.store');
        Route::get('/banners/{id}/edit', [BannerController::class, 'edit'])->can('edit banners')->name('banners.edit');
        Route::put('/banners/{id}', [BannerController::class, 'update'])->can('edit banners')->name('banners.update');
        Route::delete('/banners/{id}', [BannerController::class, 'destroy'])->can('delete banners')->name('banners.destroy');

        // General Settings Routes
        Route::get('/general-settings', [GeneralSettingController::class, 'index'])->can('view general settings')->name('general-settings.index');
        Route::get('/general-settings/create', [GeneralSettingController::class, 'create'])->can('create general settings')->name('general-settings.create');
        Route::post('/general-settings', [GeneralSettingController::class, 'store'])->can('create general settings')->name('general-settings.store');
        Route::get('/general-settings/{id}/edit', [GeneralSettingController::class, 'edit'])->can('edit general settings')->name('general-settings.edit');
        Route::put('/general-settings/{id}', [GeneralSettingController::class, 'update'])->can('edit general settings')->name('general-settings.update');
        Route::delete('/general-settings/{id}', [GeneralSettingController::class, 'destroy'])->can('delete general settings')->name('general-settings.destroy');

        // Enquiries Routes
        Route::get('/enquiries', [EnquiryController::class, 'index'])->can('view enquiries')->name('enquiries.index');
        Route::get('/enquiries/data', [EnquiryController::class, 'getData'])->can('view enquiries')->name('enquiries.data');
        Route::get('/enquiries/{id}', [EnquiryController::class, 'show'])->can('view enquiries')->name('enquiries.show');
        Route::delete('/enquiries/{id}', [EnquiryController::class, 'destroy'])->can('delete enquiries')->name('enquiries.destroy');

        // Routes Management
        Route::get('/routes', [RouteController::class, 'index'])->can('view routes')->name('routes.index');
        Route::get('/routes/data', [RouteController::class, 'getData'])->can('view routes')->name('routes.data');
        Route::get('/routes/create', [RouteController::class, 'create'])->can('create routes')->name('routes.create');
        Route::post('/routes', [RouteController::class, 'store'])->can('create routes')->name('routes.store');
        Route::get('/routes/{id}/edit', [RouteController::class, 'edit'])->can('edit routes')->name('routes.edit');
        Route::put('/routes/{id}', [RouteController::class, 'update'])->can('edit routes')->name('routes.update');
        Route::delete('/routes/{id}', [RouteController::class, 'destroy'])->can('delete routes')->name('routes.destroy');
        Route::get('/routes/{id}/stops', [RouteController::class, 'stops'])->can('view routes')->name('routes.stops');
        Route::post('/routes/{id}/stops', [RouteController::class, 'storeStop'])->can('create routes')->name('routes.stops.store');
        Route::get('/routes/{id}/stops/{stopId}/data', [RouteController::class, 'getStopData'])->can('view routes')->name('routes.stops.data');
        Route::put('/routes/{id}/stops/{stopId}', [RouteController::class, 'updateStop'])->can('edit routes')->name('routes.stops.update');
        Route::delete('/routes/{id}/stops/{stopId}', [RouteController::class, 'destroyStop'])->can('delete routes')->name('routes.stops.destroy');
        Route::get('/routes/{id}/fares', [RouteController::class, 'fares'])->can('view route fares')->name('routes.fares');
        Route::post('/routes/{id}/fares', [RouteController::class, 'storeFares'])->can('create route fares')->name('routes.fares.store');

        // Route Stops Management
        Route::get('/route-stops', [RouteStopController::class, 'index'])->can('view route stops')->name('route-stops.index');
        Route::get('/route-stops/data', [RouteStopController::class, 'getData'])->can('view route stops')->name('route-stops.data');
        Route::get('/route-stops/{id}/edit', [RouteStopController::class, 'edit'])->can('edit route stops')->name('route-stops.edit');
        Route::put('/route-stops/{id}', [RouteStopController::class, 'update'])->can('edit route stops')->name('route-stops.update');
        Route::delete('/route-stops/{id}', [RouteStopController::class, 'destroy'])->can('delete route stops')->name('route-stops.destroy');

        // Route Fares Management
        Route::get('/route-fares', [RouteFareController::class, 'index'])->can('view route fares')->name('route-fares.index');
        Route::get('/route-fares/data', [RouteFareController::class, 'getData'])->can('view route fares')->name('route-fares.data');

        // // Route Timetables Management
        // Route::get('/route-timetables', [RouteTimetableController::class, 'index'])->can('view route timetables')->name('route-timetables.index');
        // Route::get('/route-timetables/data', [RouteTimetableController::class, 'getData'])->can('view route timetables')->name('route-timetables.getData');
        // Route::get('/route-timetables/create', [RouteTimetableController::class, 'create'])->can('create route timetables')->name('route-timetables.create');
        // Route::post('/route-timetables', [RouteTimetableController::class, 'store'])->can('create route timetables')->name('route-timetables.store');
        // Route::get('/route-timetables/{routeTimetable}', [RouteTimetableController::class, 'show'])->can('view route timetables')->name('route-timetables.show');
        // Route::get('/route-timetables/{routeTimetable}/edit', [RouteTimetableController::class, 'edit'])->can('edit route timetables')->name('route-timetables.edit');
        // Route::put('/route-timetables/{routeTimetable}', [RouteTimetableController::class, 'update'])->can('edit route timetables')->name('route-timetables.update');
        // Route::delete('/route-timetables/{routeTimetable}', [RouteTimetableController::class, 'destroy'])->can('delete route timetables')->name('route-timetables.destroy');
        // Route::patch('/route-timetables/{routeTimetable}/toggle-status', [RouteTimetableController::class, 'toggleStatus'])->can('edit route timetables')->name('route-timetables.toggle-status');

        // // Route Stop Times Management
        // // Route::get('/route-timetables/{routeTimetable}/stop-times/create', [RouteStopTimeController::class, 'create'])->can('create route stop times')->name('route-stop-times.create');
        // Route::post('/route-timetables/{routeTimetable}/stop-times', [RouteStopTimeController::class, 'store'])->can('create route stop times')->name('route-stop-times.store');
        // Route::get('/route-timetables/{routeTimetable}/stop-times/edit', [RouteStopTimeController::class, 'edit'])->can('edit route stop times')->name('route-stop-times.edit');
        // Route::put('/route-timetables/{routeTimetable}/stop-times', [RouteStopTimeController::class, 'update'])->can('edit route stop times')->name('route-stop-times.update');
        // Route::delete('/route-timetables/{routeTimetable}/stop-times', [RouteStopTimeController::class, 'destroy'])->can('delete route stop times')->name('route-stop-times.destroy');
        // Route::get('/route-timetables/{routeTimetable}/stop-times/generate', [RouteStopTimeController::class, 'generate'])->can('create route stop times')->name('route-stop-times.generate');

        // Schedules Management
        Route::get('/schedules', [ScheduleController::class, 'index'])->can('view schedules')->name('schedules.index');
        Route::get('/schedules/data', [ScheduleController::class, 'getData'])->can('view schedules')->name('schedules.getData');
        Route::get('/schedules/create', [ScheduleController::class, 'create'])->can('create schedules')->name('schedules.create');
        Route::post('/schedules', [ScheduleController::class, 'store'])->can('create schedules')->name('schedules.store');
        Route::get('/schedules/{schedule}', [ScheduleController::class, 'show'])->can('view schedules')->name('schedules.show');
        Route::get('/schedules/{schedule}/edit', [ScheduleController::class, 'edit'])->can('edit schedules')->name('schedules.edit');
        Route::put('/schedules/{schedule}', [ScheduleController::class, 'update'])->can('edit schedules')->name('schedules.update');
        Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy'])->can('delete schedules')->name('schedules.destroy');
        Route::patch('/schedules/{schedule}/toggle-status', [ScheduleController::class, 'toggleStatus'])->can('edit schedules')->name('schedules.toggle-status');
    });
});

require __DIR__ . '/auth.php';
