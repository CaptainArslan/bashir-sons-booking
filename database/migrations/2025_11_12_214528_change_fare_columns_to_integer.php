<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update fares table - convert existing decimal values to integers (round to nearest integer)
        DB::statement('UPDATE fares SET base_fare = ROUND(base_fare), discount_value = ROUND(discount_value), final_fare = ROUND(final_fare)');

        Schema::table('fares', function (Blueprint $table) {
            $table->integer('base_fare')->change();
            $table->integer('discount_value')->default(0)->change();
            $table->integer('final_fare')->default(0)->change();
        });

        // Update bookings table - convert existing decimal values to integers
        DB::statement('UPDATE bookings SET total_fare = ROUND(total_fare), discount_amount = ROUND(discount_amount), tax_amount = ROUND(tax_amount), final_amount = ROUND(final_amount), payment_received_from_customer = ROUND(payment_received_from_customer), return_after_deduction_from_customer = ROUND(return_after_deduction_from_customer)');

        Schema::table('bookings', function (Blueprint $table) {
            $table->integer('total_fare')->change();
            $table->integer('discount_amount')->default(0)->change();
            $table->integer('tax_amount')->default(0)->change();
            $table->integer('final_amount')->change();
            $table->integer('payment_received_from_customer')->default(0)->change();
            $table->integer('return_after_deduction_from_customer')->default(0)->change();
        });

        // Update booking_seats table - convert existing decimal values to integers
        DB::statement('UPDATE booking_seats SET fare = ROUND(fare), tax_amount = ROUND(tax_amount), final_amount = ROUND(final_amount)');

        Schema::table('booking_seats', function (Blueprint $table) {
            $table->integer('fare')->change();
            $table->integer('tax_amount')->default(0)->change();
            $table->integer('final_amount')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert fares table
        Schema::table('fares', function (Blueprint $table) {
            DB::statement('ALTER TABLE fares MODIFY COLUMN base_fare DECIMAL(10,2) NOT NULL');
            DB::statement('ALTER TABLE fares MODIFY COLUMN discount_value DECIMAL(10,2) DEFAULT 0');
            DB::statement('ALTER TABLE fares MODIFY COLUMN final_fare DECIMAL(10,2) DEFAULT 0');
        });

        // Revert bookings table
        Schema::table('bookings', function (Blueprint $table) {
            DB::statement('ALTER TABLE bookings MODIFY COLUMN total_fare DECIMAL(10,2) NOT NULL');
            DB::statement('ALTER TABLE bookings MODIFY COLUMN discount_amount DECIMAL(10,2) DEFAULT 0');
            DB::statement('ALTER TABLE bookings MODIFY COLUMN tax_amount DECIMAL(10,2) DEFAULT 0');
            DB::statement('ALTER TABLE bookings MODIFY COLUMN final_amount DECIMAL(10,2) NOT NULL');
            DB::statement('ALTER TABLE bookings MODIFY COLUMN payment_received_from_customer DECIMAL(10,2) DEFAULT 0');
            DB::statement('ALTER TABLE bookings MODIFY COLUMN return_after_deduction_from_customer DECIMAL(10,2) DEFAULT 0');
        });

        // Revert booking_seats table
        Schema::table('booking_seats', function (Blueprint $table) {
            DB::statement('ALTER TABLE booking_seats MODIFY COLUMN fare DECIMAL(10,2) NOT NULL');
            DB::statement('ALTER TABLE booking_seats MODIFY COLUMN tax_amount DECIMAL(10,2) DEFAULT 0');
            DB::statement('ALTER TABLE booking_seats MODIFY COLUMN final_amount DECIMAL(10,2) NOT NULL');
        });
    }
};
