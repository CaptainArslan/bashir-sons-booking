<?php

namespace App\Services;

use App\Enums\SeatLockTypeEnum;
use App\Events\SeatLocked;
use App\Events\SeatReleased;
use App\Models\SeatLock;
use App\Models\Trip;
use Illuminate\Support\Facades\Redis;

class SeatService
{
    private const LOCK_PREFIX = 'seat_lock:';

    private const DEFAULT_TTL = 300; // 5 minutes

    /**
     * Get available seats for a trip segment
     */
    public function getAvailableSeats(
        int $tripId,
        int $fromStopId,
        int $toStopId
    ): array {
        $trip = Trip::with(['bus.busLayout', 'confirmedBookings.bookingSeats'])->findOrFail($tripId);

        if (! $trip->bus || ! $trip->bus->busLayout) {
            return [];
        }

        $seatMap = $trip->bus->busLayout->seat_map;
        $bookedSeats = $this->getBookedSeatsForSegment($trip, $fromStopId, $toStopId);
        $lockedSeats = $this->getLockedSeats($tripId);

        $availableSeats = [];

        foreach ($seatMap as $seat) {
            $seatId = $seat['id'] ?? "{$seat['row']}{$seat['column']}";

            if (! in_array($seatId, $bookedSeats) && ! in_array($seatId, $lockedSeats)) {
                $availableSeats[] = $seat;
            }
        }

        return $availableSeats;
    }

    /**
     * Get booked seats for a specific segment
     */
    private function getBookedSeatsForSegment(
        Trip $trip,
        int $fromStopId,
        int $toStopId
    ): array {
        $bookings = $trip->confirmedBookings()
            ->where(function ($query) use ($fromStopId, $toStopId) {
                $query->whereBetween('from_stop_id', [$fromStopId, $toStopId])
                    ->orWhereBetween('to_stop_id', [$fromStopId, $toStopId])
                    ->orWhere(function ($q) use ($fromStopId, $toStopId) {
                        $q->where('from_stop_id', '<=', $fromStopId)
                            ->where('to_stop_id', '>=', $toStopId);
                    });
            })
            ->with('bookingSeats')
            ->get();

        $bookedSeats = [];
        foreach ($bookings as $booking) {
            foreach ($booking->bookingSeats as $seat) {
                $bookedSeats[] = $seat->seat_number;
            }
        }

        return $bookedSeats;
    }

    /**
     * Lock a seat temporarily
     */
    public function lockSeat(
        int $tripId,
        string $seatId,
        array $seatData,
        SeatLockTypeEnum $lockType = SeatLockTypeEnum::Temporary,
        ?int $ttl = null
    ): bool {
        $redisKey = $this->getRedisKey($tripId, $seatId);
        $ttl = $ttl ?? $lockType->defaultTTL();

        // Check if already locked
        if (Redis::exists($redisKey)) {
            return false;
        }

        // Lock in Redis
        Redis::setex(
            $redisKey,
            $ttl,
            json_encode([
                'seat_id' => $seatId,
                'seat_data' => $seatData,
                'locked_at' => now()->toISOString(),
                'lock_type' => $lockType->value,
                'session_id' => session()->getId(),
            ])
        );

        // Create audit record in database
        $seatLock = SeatLock::create([
            'trip_id' => $tripId,
            'seat_id' => $seatId,
            'seat_number' => $seatData['number'] ?? $seatId,
            'seat_row' => $seatData['row'] ?? '',
            'seat_column' => $seatData['column'] ?? '',
            'lock_type' => $lockType,
            'locked_at' => now(),
            'expires_at' => $ttl > 0 ? now()->addSeconds($ttl) : null,
            'metadata' => json_encode(['session_id' => session()->getId()]),
        ]);

        // Broadcast event
        event(new SeatLocked($tripId, $seatId, $seatData));

        return true;
    }

    /**
     * Release a seat lock
     */
    public function releaseSeat(int $tripId, string $seatId): bool
    {
        $redisKey = $this->getRedisKey($tripId, $seatId);

        // Release from Redis
        Redis::del($redisKey);

        // Update database record
        SeatLock::where('trip_id', $tripId)
            ->where('seat_id', $seatId)
            ->whereNull('released_at')
            ->update(['released_at' => now()]);

        // Broadcast event
        event(new SeatReleased($tripId, $seatId));

        return true;
    }

    /**
     * Release multiple seats
     */
    public function releaseSeats(int $tripId, array $seatIds): void
    {
        foreach ($seatIds as $seatId) {
            $this->releaseSeat($tripId, $seatId);
        }
    }

    /**
     * Get locked seats from Redis
     */
    public function getLockedSeats(int $tripId): array
    {
        $pattern = self::LOCK_PREFIX.$tripId.':*';
        $keys = Redis::keys($pattern);

        $lockedSeats = [];
        foreach ($keys as $key) {
            $seatId = str_replace(self::LOCK_PREFIX.$tripId.':', '', $key);
            $lockedSeats[] = $seatId;
        }

        return $lockedSeats;
    }

    /**
     * Extend seat lock duration
     */
    public function extendLock(int $tripId, string $seatId, int $additionalSeconds): bool
    {
        $redisKey = $this->getRedisKey($tripId, $seatId);

        if (! Redis::exists($redisKey)) {
            return false;
        }

        $ttl = Redis::ttl($redisKey);
        if ($ttl > 0) {
            Redis::expire($redisKey, $ttl + $additionalSeconds);

            // Update database record
            SeatLock::where('trip_id', $tripId)
                ->where('seat_id', $seatId)
                ->whereNull('released_at')
                ->update(['expires_at' => now()->addSeconds($ttl + $additionalSeconds)]);

            return true;
        }

        return false;
    }

    /**
     * Release expired locks
     */
    public function releaseExpiredLocks(): int
    {
        $expiredLocks = SeatLock::expired()->get();
        $count = 0;

        foreach ($expiredLocks as $lock) {
            $this->releaseSeat($lock->trip_id, $lock->seat_id);
            $count++;
        }

        return $count;
    }

    /**
     * Sync Redis with database
     */
    public function syncSeatLocks(): array
    {
        $stats = [
            'redis_cleaned' => 0,
            'database_updated' => 0,
        ];

        // Get all active locks from database
        $dbLocks = SeatLock::active()->get();

        foreach ($dbLocks as $lock) {
            $redisKey = $this->getRedisKey($lock->trip_id, $lock->seat_id);

            // If not in Redis but in DB as active, mark as released
            if (! Redis::exists($redisKey)) {
                $lock->update(['released_at' => now()]);
                $stats['database_updated']++;
            }
        }

        // Clean up expired Redis keys (shouldn't be necessary but as safety)
        $allTrips = Trip::whereDate('departure_date', '>=', now()->subDays(1))->pluck('id');

        foreach ($allTrips as $tripId) {
            $locks = $this->getLockedSeats($tripId);
            foreach ($locks as $seatId) {
                $redisKey = $this->getRedisKey($tripId, $seatId);
                $ttl = Redis::ttl($redisKey);

                if ($ttl < 0) {
                    Redis::del($redisKey);
                    $stats['redis_cleaned']++;
                }
            }
        }

        return $stats;
    }

    /**
     * Get Redis key for seat lock
     */
    private function getRedisKey(int $tripId, string $seatId): string
    {
        return self::LOCK_PREFIX.$tripId.':'.$seatId;
    }

    /**
     * Check if seat is available
     */
    public function isSeatAvailable(
        int $tripId,
        string $seatId,
        int $fromStopId,
        int $toStopId
    ): bool {
        $redisKey = $this->getRedisKey($tripId, $seatId);

        // Check Redis lock
        if (Redis::exists($redisKey)) {
            return false;
        }

        // Check if booked
        $trip = Trip::with('confirmedBookings.bookingSeats')->findOrFail($tripId);
        $bookedSeats = $this->getBookedSeatsForSegment($trip, $fromStopId, $toStopId);

        return ! in_array($seatId, $bookedSeats);
    }

    /**
     * Get seat lock info
     */
    public function getSeatLockInfo(int $tripId, string $seatId): ?array
    {
        $redisKey = $this->getRedisKey($tripId, $seatId);

        if (! Redis::exists($redisKey)) {
            return null;
        }

        $data = json_decode(Redis::get($redisKey), true);
        $ttl = Redis::ttl($redisKey);

        return array_merge($data, [
            'ttl' => $ttl,
            'expires_at' => now()->addSeconds($ttl)->toISOString(),
        ]);
    }
}
