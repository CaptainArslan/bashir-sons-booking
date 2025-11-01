<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingPassenger;
use App\Models\BookingSeat;
use App\Models\Trip;
use App\Models\TripStop;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class BookingService
{
    public function create(array $data, ?User $actor): Booking
    {
        return DB::transaction(function () use ($data, $actor) {
            $trip = Trip::lockForUpdate()->with(['bus', 'route'])->findOrFail($data['trip_id']);

            $from = TripStop::whereKey($data['from_stop_id'])->where('trip_id', $trip->id)->firstOrFail();
            $to = TripStop::whereKey($data['to_stop_id'])->where('trip_id', $trip->id)->firstOrFail();

            // Late booking block
            if (now()->gte($from->departure_at)) {
                throw ValidationException::withMessages(['time' => 'Departure already passed for the origin stop.']);
            }

            // Forward-only segment
            if ($from->sequence >= $to->sequence) {
                throw ValidationException::withMessages(['segment' => 'Invalid segment direction.']);
            }

            // Resolve seats (recheck inside lock)
            $need = max(1, count($data['seat_numbers'] ?? $data['seats_data'] ?? []));
            $availSvc = app(AvailabilityService::class);

            $requested = $data['seat_numbers'] ?? [];
            if ($requested) {
                $free = $availSvc->availableSeats($trip->id, $from->id, $to->id);
                $freeSet = array_flip($free);
                foreach ($requested as $sn) {
                    if (! isset($freeSet[$sn])) {
                        throw ValidationException::withMessages(['seats' => "Seat $sn not available for this segment."]);
                    }
                }
                $seatNumbers = array_slice($requested, 0, $need);
            } else {
                $seatNumbers = $availSvc->availableSeats($trip->id, $from->id, $to->id, $need);
                if (count($seatNumbers) < $need) {
                    throw ValidationException::withMessages(['seats' => 'Not enough seats available.']);
                }
            }


            // Statuses by channel
            $channel = $data['channel']; // counter|phone|online
            $status = $channel === 'phone' ? 'hold' : 'confirmed';
            $paymentStatus = $channel === 'counter' ? 'paid' : 'unpaid';
            $method = $channel === 'counter' ? 'cash' : ($channel === 'online' ? 'gateway' : 'none');
            $reservedSeatsTimeout = config('app.reserved_seats_timeout', 30);
            $reservedUntil = $channel === 'phone' ? $from->departure_at->copy()->subMinutes($reservedSeatsTimeout) : null;

            $booking = Booking::create([
                'booking_number' => $this->pnr(),
                'trip_id' => $trip->id,
                'created_by_type' => $actor?->role ?? 'employee',
                'user_id' => $data['user_id'] ?? null,
                'booked_by_user_id' => $actor?->id,
                'terminal_id' => $data['terminal_id'] ?? null, // source terminal
                'from_stop_id' => $from->id,
                'to_stop_id' => $to->id,
                'channel' => $channel,
                'status' => $status,
                'reserved_until' => $reservedUntil,
                'payment_status' => $paymentStatus,
                'payment_method' => $method,
                'online_transaction_id' => $data['online_transaction_id'] ?? null,
                'total_fare' => $data['total_fare'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'tax_amount' => $data['tax_amount'] ?? 0,
                'final_amount' => $data['final_amount'] ?? 0,
                'currency' => $data['currency'] ?? ($trip->route->base_currency ?? 'PKR'),
                'total_passengers' => $need,
                'notes' => $data['notes'] ?? null,
                'payment_received_from_customer' => $data['payment_received_from_customer'] ?? null,
                'return_after_deduction_from_customer' => $data['return_after_deduction_from_customer'] ?? null,
                'confirmed_at' => $status === 'confirmed' ? now() : null,
            ]);

            // Create a map of seat_number => gender from seats_data
            $seatGenderMap = [];
            if (! empty($data['seats_data']) && is_array($data['seats_data'])) {
                foreach ($data['seats_data'] as $seatData) {
                    if (isset($seatData['seat_number']) && isset($seatData['gender'])) {
                        $seatGenderMap[$seatData['seat_number']] = $seatData['gender'];
                    }
                }
            }
            
            foreach ($seatNumbers as $sn) {
                // Calculate per-seat fare and amounts
                $seatCount = count($seatNumbers);
                $farePerSeat = $seatCount > 0 ? ($data['total_fare'] ?? 0) / $seatCount : 0;
                $taxPerSeat = $seatCount > 0 ? ($data['tax_amount'] ?? 0) / $seatCount : 0;
                $finalPerSeat = $farePerSeat + $taxPerSeat;

                // Get gender for this seat from seats_data map, default to 'male' if not found
                $seatGender = $seatGenderMap[$sn] ?? 'male';

                BookingSeat::create([
                    'booking_id' => $booking->id,
                    'from_stop_id' => $from->id,
                    'to_stop_id' => $to->id,
                    'seat_number' => (string) $sn,
                    'gender' => $seatGender,
                    'fare' => $farePerSeat,
                    'tax_amount' => $taxPerSeat,
                    'final_amount' => $finalPerSeat,
                ]);
            }

            foreach ($data['passengers'] ?? [] as $p) {
                BookingPassenger::create([
                    'booking_id' => $booking->id,
                    'name' => $p['name'],
                    'age' => $p['age'] ?? null,
                    'gender' => $p['gender'] ?? null,
                    'cnic' => $p['cnic'] ?? null,
                    'phone' => $p['phone'] ?? null,
                    'email' => $p['email'] ?? null,
                    'status' => 'active',
                ]);
            }

            return $booking->load(['seats', 'passengers', 'fromStop.terminal', 'toStop.terminal']);
        });
    }

    public function confirmPayment(Booking $booking, string $method, float $amount): void
    {
        if (in_array($booking->status, ['expired', 'cancelled'])) {
            throw new RuntimeException('Cannot confirm payment for expired/cancelled booking.');
        }
        $booking->payment_status = 'paid';
        $booking->payment_method = $method;
        $booking->status = 'confirmed';
        $booking->confirmed_at = now();
        $booking->save();
    }

    private function pnr(): string
    {
        return 'B' . strtoupper(bin2hex(random_bytes(3)));
    }
}
