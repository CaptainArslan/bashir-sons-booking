<?php

namespace App\Http\Controllers;

use App\Enums\TripStatusEnum;
use App\Http\Requests\AssignBusToTripRequest;
use App\Services\TripService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function __construct(
        private TripService $tripService
    ) {}

    /**
     * Get list of trips
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'route_id' => 'nullable|integer|exists:routes,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|string',
        ]);

        try {
            $routeId = $request->input('route_id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            if ($routeId) {
                $trips = $this->tripService->getTripsForRoute($routeId, $startDate, $endDate);
            } else {
                $trips = $this->tripService->getUpcomingTrips(
                    $request->input('limit', 10)
                );
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Trips retrieved successfully',
                'data' => $trips,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get trip details
     */
    public function show(int $id): JsonResponse
    {
        try {
            $trip = \App\Models\Trip::with([
                'route.routeStops.terminal',
                'bus.busLayout',
                'timetable',
                'bookings.bookingSeats',
                'expenses',
            ])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Trip retrieved successfully',
                'data' => $trip,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Assign bus to trip
     */
    public function assignBus(int $id, AssignBusToTripRequest $request): JsonResponse
    {
        try {
            $this->tripService->assignBus($id, $request->bus_id);

            return response()->json([
                'status' => 'success',
                'message' => 'Bus assigned to trip successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Start a trip
     */
    public function start(int $id): JsonResponse
    {
        try {
            $this->tripService->startTrip($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Trip started successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Complete a trip
     */
    public function complete(int $id): JsonResponse
    {
        try {
            $this->tripService->completeTrip($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Trip completed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Cancel a trip
     */
    public function cancel(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->tripService->cancelTrip($id, $request->reason);

            return response()->json([
                'status' => 'success',
                'message' => 'Trip cancelled successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update trip status
     */
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        try {
            $status = TripStatusEnum::from($request->status);
            $this->tripService->updateStatus($id, $status);

            return response()->json([
                'status' => 'success',
                'message' => 'Trip status updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get trip statistics
     */
    public function statistics(int $id): JsonResponse
    {
        try {
            $statistics = $this->tripService->getTripStatistics($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Trip statistics retrieved successfully',
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get trips requiring bus assignment
     */
    public function requiresBusAssignment(Request $request): JsonResponse
    {
        try {
            $daysAhead = $request->input('days_ahead', 7);
            $trips = $this->tripService->getTripsRequiringBusAssignment($daysAhead);

            return response()->json([
                'status' => 'success',
                'message' => 'Trips requiring bus assignment retrieved successfully',
                'data' => $trips,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Generate trips from timetables
     */
    public function generateFromTimetables(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $count = $this->tripService->generateTripsFromTimetables(
                $request->start_date,
                $request->end_date
            );

            return response()->json([
                'status' => 'success',
                'message' => "{$count} trips generated successfully",
                'data' => ['count' => $count],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
