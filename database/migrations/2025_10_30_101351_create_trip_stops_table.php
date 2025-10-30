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
        Schema::create('trip_stops', function (Blueprint $table) {
            $table->id();

            // Core relationships
            $table->foreignId('trip_id')
                ->constrained('trips')
                ->cascadeOnDelete();

            $table->foreignId('terminal_id')
                ->constrained('terminals')
                ->restrictOnDelete();

            // Position of this terminal along the trip route
            $table->unsignedInteger('sequence')->comment('Order of stop along the trip route');

            // Real timestamps for each trip stop (specific date + time)
            $table->dateTime('arrival_at')->nullable()->comment('Planned arrival time at this stop');
            $table->dateTime('departure_at')->nullable()->comment('Planned departure time from this stop');

            // Operational flags
            $table->boolean('is_active')->default(true)->comment('Whether this stop is currently active in the trip');
            $table->boolean('is_origin')->default(false)->comment('True if this is the starting terminal of the trip');
            $table->boolean('is_destination')->default(false)->comment('True if this is the final terminal of the trip');

            // Soft state tracking (for delay, actual timestamps, etc.)
            $table->dateTime('actual_arrival_at')->nullable();
            $table->dateTime('actual_departure_at')->nullable();
            $table->string('remarks')->nullable();

            $table->timestamps();

            // Constraints and indexes
            $table->unique(['trip_id', 'terminal_id'], 'trip_stop_unique');
            $table->unique(['trip_id', 'sequence'], 'trip_stop_sequence_unique');
            $table->index(['trip_id', 'sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_stops');
    }
};
