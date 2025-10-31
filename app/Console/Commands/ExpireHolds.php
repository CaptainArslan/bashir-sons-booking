<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;
use App\Enums\BookingStatusEnum;

class ExpireHolds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:expire-holds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire holds that are past their reserved until time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $n = Booking::where('status', BookingStatusEnum::HOLD->value)
            ->whereNotNull('reserved_until')
            ->where('reserved_until', '<=', now())
            ->update(['status' => BookingStatusEnum::EXPIRED->value]);

        $this->info("Expired $n holds.");
    }
}
