<?php

namespace App\Console\Commands;

use App\Models\Terminal;
use App\Models\City;
use App\Enums\TerminalEnum;
use Illuminate\Console\Command;

class CreateTerminalsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'terminals:create {--count=5 : Number of terminals to create}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create 5 terminals of your choice with custom details';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->option('count');
        
        $this->info("🚌 Creating {$count} terminals...");
        $this->newLine();

        // Get available cities
        $cities = City::where('status', 'active')->get();
        
        if ($cities->isEmpty()) {
            $this->error('❌ No active cities found. Please run city seeder first.');
            return 1;
        }

        $this->info('Available cities:');
        foreach ($cities as $index => $city) {
            $this->line("  [{$index}] {$city->name}");
        }
        $this->newLine();

        $terminals = [];
        
        for ($i = 1; $i <= $count; $i++) {
            $this->info("📍 Creating Terminal #{$i}:");
            
            // Select city
            $cityIndex = $this->ask("Select city index for Terminal #{$i}", 0);
            $city = $cities[$cityIndex] ?? $cities->first();
            
            // Get terminal details
            $name = $this->ask("Terminal name", "Terminal {$i}");
            $code = $this->ask("Terminal code", strtoupper(substr($city->name, 0, 3) . $i));
            $address = $this->ask("Address", "Main Road, {$city->name}");
            $phone = $this->ask("Phone number", "+92-300-000000{$i}");
            $email = $this->ask("Email (optional)", "terminal{$i}@{$city->name}.com");
            $landmark = $this->ask("Landmark (optional)", "Near {$city->name} Mall");
            
            // Status selection
            $statusChoice = $this->choice("Status", ['Active', 'Inactive'], 'Active');
            $status = $statusChoice === 'Active' ? TerminalEnum::ACTIVE : TerminalEnum::INACTIVE;
            
            $terminals[] = [
                'city_id' => $city->id,
                'name' => $name,
                'code' => $code,
                'address' => $address,
                'phone' => $phone,
                'email' => $email ?: null,
                'landmark' => $landmark ?: null,
                'latitude' => null,
                'longitude' => null,
                'status' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $this->info("✅ Terminal #{$i} details collected");
            $this->newLine();
        }

        // Confirm creation
        $this->table(
            ['#', 'Name', 'Code', 'City', 'Address', 'Phone', 'Status'],
            collect($terminals)->map(function ($terminal, $index) use ($cities) {
                $city = $cities->firstWhere('id', $terminal['city_id']);
                return [
                    $index + 1,
                    $terminal['name'],
                    $terminal['code'],
                    $city->name,
                    $terminal['address'],
                    $terminal['phone'],
                    $terminal['status']->value,
                ];
            })
        );

        if ($this->confirm('Do you want to create these terminals?')) {
            try {
                Terminal::insert($terminals);
                
                $this->newLine();
                $this->info("🎉 Successfully created {$count} terminals!");
                $this->newLine();
                
                // Show created terminals
                $this->info('Created terminals:');
                foreach ($terminals as $index => $terminal) {
                    $city = $cities->firstWhere('id', $terminal['city_id']);
                    $this->line("  ✅ {$terminal['name']} ({$terminal['code']}) - {$city->name}");
                }
                
                return 0;
            } catch (\Exception $e) {
                $this->error("❌ Error creating terminals: " . $e->getMessage());
                return 1;
            }
        } else {
            $this->info('❌ Terminal creation cancelled.');
            return 0;
        }
    }
}