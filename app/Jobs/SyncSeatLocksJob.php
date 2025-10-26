<?php

namespace App\Jobs;

use App\Services\SeatService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncSeatLocksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {}

    /**
     * Execute the job.
     */
    public function handle(SeatService $seatService): void
    {
        try {
            // Release expired locks
            $expiredCount = $seatService->releaseExpiredLocks();

            // Sync Redis with database
            $syncStats = $seatService->syncSeatLocks();

            Log::info('Synced seat locks', [
                'expired_released' => $expiredCount,
                'redis_cleaned' => $syncStats['redis_cleaned'],
                'database_updated' => $syncStats['database_updated'],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to sync seat locks', [
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
        return ['seats', 'locks', 'sync'];
    }
}
