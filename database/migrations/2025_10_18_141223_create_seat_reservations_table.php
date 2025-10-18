<?php

use App\Enums\BookingChannelEnum;
use Illuminate\Support\Facades\Schema;
use App\Enums\SeatReservationStatusEnum;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seat_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_instance_id')->constrained('trip_instances')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('booking_passenger_id')->constrained('booking_passengers')->cascadeOnDelete();
            $table->unsignedInteger('seat_number');
            $table->timestamp('hold_expires_at')->nullable();
            $table->string('channel')->default(BookingChannelEnum::COUNTER->value);
            $table->string('status')->default(SeatReservationStatusEnum::PENDING->value);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seat_reservations');
    }
};
