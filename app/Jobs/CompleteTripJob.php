<?php

namespace App\Jobs;

use App\Services\TripLifecycleService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CompleteTripJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {}

    /**
     * Execute the job.
     */
    public function handle(TripLifecycleService $lifecycleService): void
    {
        try {
            $completedCount = $lifecycleService->processCompletedTrips();

            Log::info('Completed trips', [
                'count' => $completedCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to complete trips', [
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
        return ['trips', 'lifecycle', 'complete'];
    }
}
