<?php

namespace App\Http\Controllers;

use App\Enums\SeatLockTypeEnum;
use App\Services\SeatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeatController extends Controller
{
    public function __construct(
        private SeatService $seatService
    ) {}

    /**
     * Get available seats for a trip segment
     */
    public function available(Request $request): JsonResponse
    {
        $request->validate([
            'trip_id' => 'required|integer|exists:trips,id',
            'from_stop_id' => 'required|integer|exists:route_stops,id',
            'to_stop_id' => 'required|integer|exists:route_stops,id',
        ]);

        try {
            $seats = $this->seatService->getAvailableSeats(
                $request->trip_id,
                $request->from_stop_id,
                $request->to_stop_id
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Available seats retrieved successfully',
                'data' => [
                    'seats' => $seats,
                    'count' => count($seats),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Lock a seat
     */
    public function lock(Request $request): JsonResponse
    {
        $request->validate([
            'trip_id' => 'required|integer|exists:trips,id',
            'seat_id' => 'required|string',
            'seat_data' => 'required|array',
            'seat_data.number' => 'required|string',
            'seat_data.row' => 'required|string',
            'seat_data.column' => 'required|string',
            'lock_type' => 'nullable|string',
            'ttl' => 'nullable|integer|min:1',
        ]);

        try {
            $lockType = $request->lock_type
                ? SeatLockTypeEnum::from($request->lock_type)
                : SeatLockTypeEnum::Temporary;

            $locked = $this->seatService->lockSeat(
                $request->trip_id,
                $request->seat_id,
                $request->seat_data,
                $lockType,
                $request->ttl
            );

            if ($locked) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Seat locked successfully',
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Seat is already locked',
            ], 409);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Release a seat lock
     */
    public function release(Request $request): JsonResponse
    {
        $request->validate([
            'trip_id' => 'required|integer|exists:trips,id',
            'seat_id' => 'required|string',
        ]);

        try {
            $this->seatService->releaseSeat($request->trip_id, $request->seat_id);

            return response()->json([
                'status' => 'success',
                'message' => 'Seat released successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get locked seats for a trip
     */
    public function locked(int $tripId): JsonResponse
    {
        try {
            $lockedSeats = $this->seatService->getLockedSeats($tripId);

            return response()->json([
                'status' => 'success',
                'message' => 'Locked seats retrieved successfully',
                'data' => [
                    'seats' => $lockedSeats,
                    'count' => count($lockedSeats),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Check if seat is available
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'trip_id' => 'required|integer|exists:trips,id',
            'seat_id' => 'required|string',
            'from_stop_id' => 'required|integer|exists:route_stops,id',
            'to_stop_id' => 'required|integer|exists:route_stops,id',
        ]);

        try {
            $available = $this->seatService->isSeatAvailable(
                $request->trip_id,
                $request->seat_id,
                $request->from_stop_id,
                $request->to_stop_id
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Seat availability checked successfully',
                'data' => [
                    'available' => $available,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get seat lock info
     */
    public function lockInfo(Request $request): JsonResponse
    {
        $request->validate([
            'trip_id' => 'required|integer|exists:trips,id',
            'seat_id' => 'required|string',
        ]);

        try {
            $info = $this->seatService->getSeatLockInfo(
                $request->trip_id,
                $request->seat_id
            );

            if ($info) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Seat lock info retrieved successfully',
                    'data' => $info,
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Seat is not locked',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Extend seat lock
     */
    public function extendLock(Request $request): JsonResponse
    {
        $request->validate([
            'trip_id' => 'required|integer|exists:trips,id',
            'seat_id' => 'required|string',
            'additional_seconds' => 'required|integer|min:1|max:600',
        ]);

        try {
            $extended = $this->seatService->extendLock(
                $request->trip_id,
                $request->seat_id,
                $request->additional_seconds
            );

            if ($extended) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Seat lock extended successfully',
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Unable to extend seat lock',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
