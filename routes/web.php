<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckUserStatus;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\BusController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\Admin\Citycontroller;
use App\Http\Controllers\Admin\FareController;
use App\Http\Controllers\Admin\Rolecontroller;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RouteController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\BusTypeController;
use App\Http\Controllers\Admin\EnquiryController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\Admin\BusLayoutController;
use App\Http\Controllers\Admin\RouteStopController;
use App\Http\Controllers\Admin\TimetableController;
use App\Http\Controllers\FrontendBookingController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\AdvanceBookingController;
use App\Http\Controllers\Admin\GeneralSettingController;
use App\Http\Controllers\Admin\TerminalReportController;
use App\Http\Controllers\Admin\CounterTerminalController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;

// use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;

Route::get('/', function () {
    return redirect()->route('home');
});

Route::get('/home', [DashboardController::class, 'home'])->name('home');
Route::get('/services', [DashboardController::class, 'services'])->name('services');
Route::get('/bookings', [DashboardController::class, 'bookings'])->name('bookings');
Route::get('/about-us', [DashboardController::class, 'aboutUs'])->name('about-us');
Route::get('/contact', [DashboardController::class, 'contact'])->name('contact');
Route::post('/enquiry', [DashboardController::class, 'submitEnquiry'])->name('enquiry.submit');

// Frontend AJAX Routes
Route::get('/api/route-stops', [DashboardController::class, 'getRouteStops'])->name('frontend.route-stops');
Route::get('/api/departure-times', [DashboardController::class, 'getDepartureTimes'])->name('frontend.departure-times');

// Frontend Booking Routes
Route::prefix('bookings')->name('frontend.bookings.')->group(function () {
    Route::get('/trips', [FrontendBookingController::class, 'showTrips'])->name('trips');
    Route::get('/trips/load', [FrontendBookingController::class, 'loadTrips'])->name('load-trips');
    Route::get('/seats', [FrontendBookingController::class, 'selectSeats'])->name('select-seats')->middleware('auth');
    Route::get('/trip-details', [FrontendBookingController::class, 'loadTripDetails'])->name('load-trip-details')->middleware('auth');
    Route::post('/store', [FrontendBookingController::class, 'store'])->name('store')->middleware('auth');

    // Payment Routes
    Route::middleware('auth')->group(function () {
        Route::get('/{booking}/payment', [PaymentController::class, 'show'])->name('payment');
        Route::post('/{booking}/payment', [PaymentController::class, 'process'])->name('payment.process');
        Route::get('/{booking}/success', [PaymentController::class, 'success'])->name('success');
    });
});

// Frontend Routes
Route::middleware(['guest', '2fa.pending'])->group(function () {
    Route::get('/two-factor-challenge', [TwoFactorController::class, 'challenge'])->name('2fa.challenge');
    Route::post('/two-factor-challenge', [TwoFactorController::class, 'verifyChallenge'])->name('2fa.verify');
});

Route::middleware(['auth', CheckUserStatus::class])->group(function () {
    // Profile routes - now using frontend views
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/bookings', [ProfileController::class, 'bookings'])->name('profile.bookings');

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
        Route::post('/users/{id}/ban', [UserController::class, 'ban'])->can('ban users')->name('users.ban');
        Route::post('/users/{id}/activate', [UserController::class, 'activate'])->can('activate users')->name('users.activate');

        // Employees Routes
        Route::get('/employees', [EmployeeController::class, 'index'])->can('manage users')->name('employees.index');
        Route::get('/employees/data', [EmployeeController::class, 'getData'])->can('manage users')->name('employees.data');
        Route::get('/employees/stats', [EmployeeController::class, 'stats'])->can('manage users')->name('employees.stats');
        Route::get('/employees/create', [EmployeeController::class, 'create'])->can('manage users')->name('employees.create');
        Route::get('/employees/routes-by-terminal', [EmployeeController::class, 'getRoutesByTerminal'])->can('manage users')->name('employees.routes-by-terminal');
        Route::post('/employees', [EmployeeController::class, 'store'])->can('manage users')->name('employees.store');
        Route::get('/employees/{id}/edit', [EmployeeController::class, 'edit'])->can('manage users')->name('employees.edit');
        Route::put('/employees/{id}', [EmployeeController::class, 'update'])->can('manage users')->name('employees.update');
        Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->can('manage users')->name('employees.destroy');
        Route::post('/employees/{id}/ban', [EmployeeController::class, 'ban'])->can('ban users')->name('employees.ban');
        Route::post('/employees/{id}/activate', [EmployeeController::class, 'activate'])->can('activate users')->name('employees.activate');

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
        Route::put('/routes/{id}/stops', [RouteController::class, 'updateStops'])->can('edit routes')->name('routes.stops.update');
        Route::get('/routes/{id}/stops/{stopId}/data', [RouteController::class, 'getStopData'])->can('view routes')->name('routes.stops.data');
        Route::put('/routes/{id}/stops/{stopId}', [RouteController::class, 'updateStop'])->can('edit routes')->name('routes.stops.update-single');
        Route::delete('/routes/{id}/stops/{stopId}', [RouteController::class, 'destroyStop'])->can('delete routes')->name('routes.stops.destroy');
        Route::get('/routes/{id}/manage-fares', [RouteController::class, 'manageFares'])->can('edit routes')->name('routes.manage-fares');
        Route::post('/routes/{id}/fares', [RouteController::class, 'storeFares'])->can('edit routes')->name('routes.fares.store');

        // Route Stops Management
        Route::get('/route-stops', [RouteStopController::class, 'index'])->can('view route stops')->name('route-stops.index');
        Route::get('/route-stops/data', [RouteStopController::class, 'getData'])->can('view route stops')->name('route-stops.data');

        // Fares Management
        Route::get('/fares', [FareController::class, 'index'])->can('view fares')->name('fares.index');
        Route::get('/fares/data', [FareController::class, 'getData'])->can('view fares')->name('fares.data');
        Route::get('/fares/check', [FareController::class, 'checkFare'])->can('view fares')->name('fares.check');
        Route::get('/fares/create', [FareController::class, 'create'])->can('create fares')->name('fares.create');
        Route::post('/fares', [FareController::class, 'store'])->can('create fares')->name('fares.store');
        Route::get('/fares/{id}/edit', [FareController::class, 'edit'])->can('edit fares')->name('fares.edit');
        Route::put('/fares/{id}', [FareController::class, 'update'])->can('edit fares')->name('fares.update');
        Route::delete('/fares/{id}', [FareController::class, 'destroy'])->can('delete fares')->name('fares.destroy');

        // Timetables Management
        Route::get('/timetables', [TimetableController::class, 'index'])->can('view timetables')->name('timetables.index');
        Route::get('/timetables/data', [TimetableController::class, 'getData'])->can('view timetables')->name('timetables.data');
        Route::get('/timetables/create', [TimetableController::class, 'create'])->can('create timetables')->name('timetables.create');
        Route::post('/timetables', [TimetableController::class, 'store'])->can('create timetables')->name('timetables.store');
        Route::get('/timetables/{timetable}', [TimetableController::class, 'show'])->can('view timetables')->name('timetables.show');
        Route::get('/timetables/{timetable}/edit', [TimetableController::class, 'edit'])->can('edit timetables')->name('timetables.edit');
        Route::put('/timetables/{timetable}', [TimetableController::class, 'update'])->can('edit timetables')->name('timetables.update');
        Route::patch('/timetables/{timetable}/toggle-status', [TimetableController::class, 'toggleStatus'])->can('edit timetables')->name('timetables.toggle-status');
        Route::patch('/timetables/{timetable}/stops/{timetableStop}/toggle-status', [TimetableController::class, 'toggleStopStatus'])->can('edit timetables')->name('timetables.stops.toggle-status');
        Route::patch('/timetables/{timetable}/stops/toggle-all', [TimetableController::class, 'toggleAllStops'])->can('edit timetables')->name('timetables.stops.toggle-all');
        Route::delete('/timetables/{timetable}', [TimetableController::class, 'destroy'])->can('delete timetables')->name('timetables.destroy');
        Route::get('/routes/{route}/stops', [TimetableController::class, 'getRouteStops'])->can('view routes')->name('routes.stops.ajax');

        // bookings Routes
        Route::get('/bookings', [BookingController::class, 'index'])->can('view all booking reports')->name('bookings.index');
        Route::get('/bookings/data', [BookingController::class, 'getData'])->can('view all booking reports')->name('bookings.data');
        Route::get('/bookings/create', [BookingController::class, 'create'])->can('create bookings')->name('bookings.create');
        Route::post('/bookings', [BookingController::class, 'store'])->can('create bookings')->name('bookings.store');
        Route::get('/bookings/{booking}', [BookingController::class, 'show'])->can('view bookings')->name('bookings.show');
        Route::get('/bookings/{booking}/edit', [BookingController::class, 'edit'])->can('edit bookings')->name('bookings.edit');
        Route::put('/bookings/{booking}', [BookingController::class, 'update'])->can('edit bookings')->name('bookings.update');
        Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->can('delete bookings')->name('bookings.destroy');
        Route::get('/bookings/{booking}/print/{type?}/{size?}', [BookingController::class, 'printTicket'])->can('view bookings')->name('bookings.print');
        Route::post('/bookings/{booking}/seats/{seat}/cancel', [BookingController::class, 'cancelSeat'])->can('edit bookings')->name('bookings.seats.cancel');
        Route::post('/bookings/{booking}/seats/{seat}/restore', [BookingController::class, 'restoreSeat'])->can('edit bookings')->name('bookings.seats.restore');

        // Booking Console Routes (Livewire Component)
        Route::get('/bookings/console/load', fn() => view('admin.bookings.console-wrapper'))->can('create bookings')->name('bookings.console');

        // Announcements Routes
        Route::get('/announcements', [AnnouncementController::class, 'index'])->can('view announcements')->name('announcements.index');
        Route::get('/announcements/data', [AnnouncementController::class, 'getData'])->can('view announcements')->name('announcements.data');
        Route::get('/announcements/create', [AnnouncementController::class, 'create'])->can('create announcements')->name('announcements.create');
        Route::post('/announcements', [AnnouncementController::class, 'store'])->can('create announcements')->name('announcements.store');
        Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show'])->can('view announcements')->name('announcements.show');
        Route::get('/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->can('edit announcements')->name('announcements.edit');
        Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->can('edit announcements')->name('announcements.update');
        Route::patch('/announcements/{announcement}/toggle-status', [AnnouncementController::class, 'toggleStatus'])->can('edit announcements')->name('announcements.toggle-status');
        Route::patch('/announcements/{announcement}/toggle-pinned', [AnnouncementController::class, 'togglePinned'])->can('edit announcements')->name('announcements.toggle-pinned');
        Route::patch('/announcements/{announcement}/toggle-featured', [AnnouncementController::class, 'toggleFeatured'])->can('edit announcements')->name('announcements.toggle-featured');
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->can('delete announcements')->name('announcements.destroy');

        // Advance Booking Routes
        Route::get('/advance-booking', [AdvanceBookingController::class, 'index'])->can('edit general settings')->name('advance-booking.index');
        Route::put('/advance-booking', [AdvanceBookingController::class, 'update'])->can('edit general settings')->name('advance-booking.update');
        Route::patch('/advance-booking/toggle-status', [AdvanceBookingController::class, 'toggleStatus'])->can('edit general settings')->name('advance-booking.toggle-status');
        Route::get('/advance-booking/settings', [AdvanceBookingController::class, 'getSettings'])->can('view general settings')->name('advance-booking.settings');

        // Terminal Reports Routes
        Route::get('/terminal-reports', [TerminalReportController::class, 'index'])->can('view terminal reports')->name('terminal-reports.index');
        Route::get('/terminal-reports/data', [TerminalReportController::class, 'getData'])->can('view terminal reports')->name('terminal-reports.data');
        Route::get('/terminal-reports/bookings-data', [TerminalReportController::class, 'getBookingsData'])->can('view terminal reports')->name('terminal-reports.bookings-data');
        Route::get('/terminal-reports/export', [TerminalReportController::class, 'export'])->can('view terminal reports')->name('terminal-reports.export');

        // Sales Reports Routes (Admin)
        Route::get('/reports', [AdminReportController::class, 'index'])->can('view bookings')->name('reports.index');
        Route::get('/reports/sales', [AdminReportController::class, 'sales'])->can('view bookings')->name('reports.sales');

        // Discount Routes
        Route::get('/discounts', [DiscountController::class, 'index'])->can('view discounts')->name('discounts.index');
        Route::get('/discounts/data', [DiscountController::class, 'getData'])->can('view discounts')->name('discounts.data');
        Route::get('/discounts/create', [DiscountController::class, 'create'])->can('create discounts')->name('discounts.create');
        Route::post('/discounts', [DiscountController::class, 'store'])->can('create discounts')->name('discounts.store');
        Route::get('/discounts/{discount}', [DiscountController::class, 'show'])->can('view discounts')->name('discounts.show');
        Route::get('/discounts/{discount}/edit', [DiscountController::class, 'edit'])->can('edit discounts')->name('discounts.edit');
        Route::put('/discounts/{discount}', [DiscountController::class, 'update'])->can('edit discounts')->name('discounts.update');
        Route::patch('/discounts/{discount}/toggle-status', [DiscountController::class, 'toggleStatus'])->can('edit discounts')->name('discounts.toggle-status');
        Route::delete('/discounts/{discount}', [DiscountController::class, 'destroy'])->can('delete discounts')->name('discounts.destroy');
    });
});

require __DIR__ . '/auth.php';
