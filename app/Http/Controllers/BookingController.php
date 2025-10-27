<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateBookingRequest;
use App\Http\Requests\SearchTripsRequest;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookingService
    ) {}

    /**
     * Search for available trips
     */
    public function search(SearchTripsRequest $request): JsonResponse
    {
        try {
            $results = $this->bookingService->searchTrips($request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Trips retrieved successfully',
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Create a new booking
     */
    public function store(CreateBookingRequest $request): JsonResponse
    {
        try {
            $booking = $this->bookingService->createBooking($request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Booking created successfully',
                'data' => $booking,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Confirm a booking
     */
    public function confirm(string $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->confirmBooking($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Booking confirmed successfully',
                'data' => $booking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Cancel a booking
     */
    public function cancel(int $id, Request $request): JsonResponse
    {
        try {
            $reason = $request->input('reason');
            $this->bookingService->cancelBooking($id, $reason);

            return response()->json([
                'status' => 'success',
                'message' => 'Booking cancelled successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get booking details
     */
    public function show(string $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->getBookingDetails($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Booking retrieved successfully',
                'data' => $booking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get user bookings
     */
    public function userBookings(Request $request): JsonResponse
    {
        try {
            $userId = $request->input('user_id', auth()->id());
            $status = $request->input('status');

            $bookings = $this->bookingService->getUserBookings($userId, $status);

            return response()->json([
                'status' => 'success',
                'message' => 'Bookings retrieved successfully',
                'data' => $bookings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Calculate fare for a route segment
     */
    public function calculateFare(Request $request): JsonResponse
    {
        $request->validate([
            'from_stop_id' => 'required|integer|exists:route_stops,id',
            'to_stop_id' => 'required|integer|exists:route_stops,id',
            'passengers' => 'required|integer|min:1',
        ]);

        try {
            $fare = $this->bookingService->calculateFare(
                $request->from_stop_id,
                $request->to_stop_id,
                $request->passengers
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Fare calculated successfully',
                'data' => $fare,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
