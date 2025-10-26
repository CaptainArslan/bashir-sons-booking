<?php

namespace App\Jobs;

use App\Services\BookingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReleasePhoneBookingsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private int $minutesBefore = 30
    ) {}

    /**
     * Execute the job.
     */
    public function handle(BookingService $bookingService): void
    {
        try {
            $releasedCount = $bookingService->releasePhoneBookingsBeforeDeparture(
                $this->minutesBefore
            );

            Log::info('Released phone bookings', [
                'count' => $releasedCount,
                'minutes_before' => $this->minutesBefore,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to release phone bookings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return ['bookings', 'phone-bookings', 'release'];
    }
}
