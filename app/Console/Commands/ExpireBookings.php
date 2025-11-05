<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireBookings extends Command
{
    protected $signature = 'bookings:expire';

    protected $description = 'Expire unpaid bookings that have passed their reservation time limit';

    public function handle(): int
    {
        $this->info('Starting to expire unpaid bookings...');

        $expiredCount = Booking::where('status', 'hold')
            ->where('payment_status', 'unpaid')
            ->whereNotNull('reserved_until')
            ->where('reserved_until', '<=', now())
            ->update([
                'status' => 'expired',
                'updated_at' => now(),
            ]);

        $this->info("Expired {$expiredCount} booking(s).");

        if ($expiredCount > 0) {
            Log::info("Expired {$expiredCount} unpaid booking(s)", [
                'command' => 'bookings:expire',
                'timestamp' => now(),
            ]);
        }

        return Command::SUCCESS;
    }
}
