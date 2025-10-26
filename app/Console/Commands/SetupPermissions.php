<?php

namespace App\Console\Commands;

use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Console\Command;

class SetupPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup permissions and roles for the application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up permissions and roles...');

        try {
            // Run permission seeder
            $this->info('Creating permissions...');
            $this->call('db:seed', ['--class' => PermissionSeeder::class]);

            // Run role seeder
            $this->info('Creating roles and assigning permissions...');
            $this->call('db:seed', ['--class' => RoleSeeder::class]);

            $this->info('Permissions and roles setup completed successfully!');

            $this->table(
                ['Role', 'Permissions Count'],
                [
                    ['Super Admin', 'All permissions'],
                    ['Admin', 'Most permissions (except system management)'],
                    ['Manager', 'Business operations management'],
                    ['Employee', 'Limited operational permissions'],
                    ['Customer', 'No admin permissions'],
                ]
            );

        } catch (\Exception $e) {
            $this->error('Error setting up permissions: '.$e->getMessage());

            return 1;
        }

        return 0;
    }
}
