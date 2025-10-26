<?php

use App\Http\Controllers\BookingController as ApiBookingController;
use App\Http\Controllers\Customer\BookingController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\TripController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public API routes for booking
Route::get('/routes/{route}/stops', function ($routeId) {
    $route = \App\Models\Route::with('routeStops.terminal.city')->findOrFail($routeId);

    return $route->routeStops->map(function ($stop) {
        return [
            'id' => $stop->id,
            'sequence' => $stop->sequence,
            'terminal' => [
                'id' => $stop->terminal->id,
                'name' => $stop->terminal->name,
                'city' => [
                    'id' => $stop->terminal->city->id,
                    'name' => $stop->terminal->city->name,
                ],
            ],
        ];
    });
});

// Get available routes between terminals
Route::get('/booking/available-routes', [BookingController::class, 'getAvailableRoutes']);

/*
|--------------------------------------------------------------------------
| Bus Ticket Booking System API Routes
|--------------------------------------------------------------------------
*/

// Public routes - Trip search and seat availability
Route::prefix('v1')->group(function () {
    Route::post('/trips/search', [ApiBookingController::class, 'search']);
    Route::post('/bookings/calculate-fare', [ApiBookingController::class, 'calculateFare']);
    Route::get('/seats/available', [SeatController::class, 'available']);
    Route::post('/seats/check-availability', [SeatController::class, 'checkAvailability']);
});

// Authenticated routes
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {

    // Booking Management
    Route::prefix('bookings')->group(function () {
        Route::post('/', [ApiBookingController::class, 'store']);
        Route::get('/my-bookings', [ApiBookingController::class, 'userBookings']);
        Route::get('/{id}', [ApiBookingController::class, 'show']);
        Route::post('/{id}/confirm', [ApiBookingController::class, 'confirm']);
        Route::post('/{id}/cancel', [ApiBookingController::class, 'cancel']);
    });

    // Seat Management
    Route::prefix('seats')->group(function () {
        Route::post('/lock', [SeatController::class, 'lock']);
        Route::post('/release', [SeatController::class, 'release']);
        Route::get('/locked/{tripId}', [SeatController::class, 'locked']);
        Route::post('/lock-info', [SeatController::class, 'lockInfo']);
        Route::post('/extend-lock', [SeatController::class, 'extendLock']);
    });

    // Trip Management (Admin/Employee only)
    Route::middleware('role:Admin|Super Admin|Employee')->prefix('trips')->group(function () {
        Route::get('/', [TripController::class, 'index']);
        Route::get('/{id}', [TripController::class, 'show']);
        Route::get('/{id}/statistics', [TripController::class, 'statistics']);
    });

    // Trip Management (Admin only)
    Route::middleware('role:Admin|Super Admin')->prefix('trips')->group(function () {
        Route::post('/{id}/assign-bus', [TripController::class, 'assignBus']);
        Route::post('/{id}/start', [TripController::class, 'start']);
        Route::post('/{id}/complete', [TripController::class, 'complete']);
        Route::post('/{id}/cancel', [TripController::class, 'cancel']);
        Route::post('/{id}/update-status', [TripController::class, 'updateStatus']);
        Route::get('/requires-bus-assignment', [TripController::class, 'requiresBusAssignment']);
        Route::post('/generate-from-timetables', [TripController::class, 'generateFromTimetables']);
    });

    // Expense Management (Admin/Employee only)
    Route::middleware('role:Admin|Super Admin|Employee')->prefix('expenses')->group(function () {
        Route::post('/', [ExpenseController::class, 'store']);
        Route::put('/{id}', [ExpenseController::class, 'update']);
        Route::get('/trip/{tripId}/summary', [ExpenseController::class, 'tripSummary']);
        Route::get('/user/{userId}', [ExpenseController::class, 'userExpenses']);
    });

    // Expense Management (Admin only)
    Route::middleware('role:Admin|Super Admin')->prefix('expenses')->group(function () {
        Route::delete('/{id}', [ExpenseController::class, 'destroy']);
        Route::get('/date-range', [ExpenseController::class, 'dateRange']);
        Route::get('/statistics', [ExpenseController::class, 'statistics']);
    });
});
