<?php

use App\Enums\GenderEnum;
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
            $table->foreignId('from_stop_id')->constrained('route_stops')->cascadeOnDelete();
            $table->foreignId('to_stop_id')->constrained('route_stops')->cascadeOnDelete();
            $table->string('seat_number');
            $table->enum('gender', GenderEnum::getGenders())->default(GenderEnum::MALE->value);
            $table->decimal('fare', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['booking_id', 'seat_number'], 'unique_booking_seat');
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
