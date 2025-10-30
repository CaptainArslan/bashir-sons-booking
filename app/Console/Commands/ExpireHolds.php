<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;

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
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $n = Booking::where('status', 'hold')
            ->whereNotNull('reserved_until')
            ->where('reserved_until', '<=', now())
            ->update(['status' => 'expired']);

        $this->info("Expired $n holds.");
    }
}
