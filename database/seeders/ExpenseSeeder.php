<?php

namespace Database\Seeders;

use App\Models\Expense;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create fuel expenses
        Expense::factory()
            ->count(20)
            ->fuel()
            ->create();

        // Create toll expenses
        Expense::factory()
            ->count(15)
            ->toll()
            ->create();

        // Create driver pay expenses
        Expense::factory()
            ->count(25)
            ->driverPay()
            ->create();

        // Create miscellaneous expenses
        Expense::factory()
            ->count(10)
            ->create();
    }
}
