<?php

use App\Enums\BookingStatusEnum;
use App\Enums\BookingChannelEnum;
use App\Enums\PaymentMethodEnum;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference_number')->unique()->nullable()->comment('Public reference number');
            // Trip & Route
            $table->foreignId('trip_instance_id')->constrained('trip_instances')->cascadeOnDelete();
            $table->foreignId('route_timetable_id')->constrained('route_timetables')->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('from_stop_id')->constrained('route_stops')->cascadeOnDelete();
            $table->foreignId('to_stop_id')->constrained('route_stops')->cascadeOnDelete();
            $table->foreignId('terminal_id')->nullable()->constrained('terminals')->nullOnDelete();

            // Booking details
            $table->string('channel')->default(BookingChannelEnum::COUNTER->value);
            $table->integer('total_seats')->comment('Total number of seats booked');

            // Fare
            $table->decimal('base_fare_per_seat', 10, 2)->comment('Base fare per seat');
            $table->decimal('total_fare', 10, 2)->comment('Total fare');
            $table->string('currency', 3)->default('PKR')->comment('The currency of the fare');

            // Status & Payment
            $table->string('status')->default(BookingStatusEnum::HELD->value);
            $table->string('payment_method')->default(PaymentMethodEnum::ONLINE->value);
            $table->string('online_payment_method')->nullable()->comment('The method used for online payment');

            // Booking lifecycle
            $table->uuid('uuid')->unique()->nullable()->comment('Unique identifier for the booking');
            $table->timestamp('expiry_at')->nullable()->comment('Expires if not confirmed');
            $table->string('remarks')->nullable()->comment('Additional remarks for the booking');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
