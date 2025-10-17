<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\BookingController;

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
                ]
            ]
        ];
    });
});
