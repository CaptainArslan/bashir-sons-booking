<?php

namespace App\Console\Commands;

use App\Models\Timetable;
use App\Models\Trip;
use App\Services\TripFactoryService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreateTripsForDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trips:create-for-day {--date= : The date to create trips for (Y-m-d format). Defaults to today}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create trips for a specific day from active timetables with existence checks';

    protected TripFactoryService $tripFactory;

    public function __construct(TripFactoryService $tripFactory)
    {
        parent::__construct();
        $this->tripFactory = $tripFactory;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Get the date (default to today)
        $dateInput = $this->option('date');
        $date = $dateInput ? Carbon::parse($dateInput)->format('Y-m-d') : Carbon::today()->format('Y-m-d');
        $dateCarbon = Carbon::parse($date);

        $this->info("Creating trips for: {$dateCarbon->format('d M Y')} (Day: {$dateCarbon->format('l')})");
        $this->newLine();

        // Get all active timetables with active routes
        $timetables = Timetable::where('is_active', true)
            ->with(['route' => function ($query) {
                $query->where('status', 'active');
            }])
            ->whereHas('route', function ($query) {
                $query->where('status', 'active');
            })
            ->whereHas('timetableStops', function ($query) {
                $query->where('is_active', true);
            })
            ->get();

        if ($timetables->isEmpty()) {
            $this->warn('No active timetables found with active routes and stops.');

            return Command::FAILURE;
        }

        $this->info("Found {$timetables->count()} active timetable(s)");
        $this->newLine();

        $created = 0;
        $skipped = 0;
        $errors = 0;
        $errorsList = [];

        $bar = $this->output->createProgressBar($timetables->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% -- %message%');
        $bar->start();

        foreach ($timetables as $timetable) {
            $bar->setMessage("Processing: {$timetable->name} (Route: {$timetable->route->name})");

            try {
                // Check if trip already exists for this timetable and date
                $existingTrip = Trip::where('timetable_id', $timetable->id)
                    ->whereDate('departure_date', $date)
                    ->first();

                if ($existingTrip) {
                    $bar->setMessage("Skipped: Trip already exists for {$timetable->name}");
                    $skipped++;
                    $bar->advance();

                    continue;
                }

                // Create the trip
                $trip = $this->tripFactory->createFromTimetable($timetable->id, $date);

                $bar->setMessage("Created: Trip #{$trip->id} for {$timetable->name}");
                $created++;
                $bar->advance();
            } catch (\Exception $e) {
                $errorMsg = "Error creating trip for {$timetable->name}: {$e->getMessage()}";
                $bar->setMessage($errorMsg);
                $errorsList[] = $errorMsg;
                $errors++;
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        // Display summary
        $this->info('=== Summary ===');
        $this->table(
            ['Status', 'Count'],
            [
                ['Created', $created],
                ['Skipped (Already Exists)', $skipped],
                ['Errors', $errors],
                ['Total Processed', $timetables->count()],
            ]
        );

        if ($errors > 0) {
            $this->newLine();
            $this->error('Errors encountered:');
            foreach ($errorsList as $error) {
                $this->line("  - {$error}");
            }
        }

        if ($created > 0) {
            $this->newLine();
            $this->info("✓ Successfully created {$created} trip(s) for {$dateCarbon->format('d M Y')}");
        }

        if ($skipped > 0) {
            $this->info("⊘ Skipped {$skipped} trip(s) that already exist");
        }

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
