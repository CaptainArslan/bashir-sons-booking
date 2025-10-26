<?php

namespace App\Jobs;

use App\Services\TripLifecycleService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class StartTripJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {}

    /**
     * Execute the job.
     */
    public function handle(TripLifecycleService $lifecycleService): void
    {
        try {
            $startedCount = $lifecycleService->processStartingTrips();

            Log::info('Started trips', [
                'count' => $startedCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to start trips', [
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
        return ['trips', 'lifecycle', 'start'];
    }
}
