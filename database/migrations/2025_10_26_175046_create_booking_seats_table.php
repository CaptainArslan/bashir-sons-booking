<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('booking_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();

            $table->string('seat_number');
            $table->string('seat_row');
            $table->string('seat_column');

            $table->string('passenger_name');
            $table->string('passenger_age')->nullable();
            $table->string('passenger_gender')->nullable();
            $table->string('passenger_cnic')->nullable();
            $table->string('passenger_phone')->nullable();

            $table->decimal('fare', 10, 2);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['booking_id', 'seat_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_seats');
    }
};
