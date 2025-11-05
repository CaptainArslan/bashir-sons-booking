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
        Schema::create('bus_assignments', function (Blueprint $table) {
            $table->id();

            // Trip and segment information
            $table->foreignId('trip_id')->constrained('trips')->cascadeOnDelete();
            $table->foreignId('from_trip_stop_id')->constrained('trip_stops')->cascadeOnDelete()
                ->comment('Starting terminal/stop for this segment');
            $table->foreignId('to_trip_stop_id')->constrained('trip_stops')->cascadeOnDelete()
                ->comment('Ending terminal/stop for this segment');

            // Bus information
            $table->foreignId('bus_id')->nullable()->constrained('buses')->nullOnDelete();

            // Driver information
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->string('driver_cnic')->nullable();
            $table->string('driver_license')->nullable();
            $table->text('driver_address')->nullable();

            // Host/Trip Attendant information (terminal to terminal)
            $table->string('host_name')->nullable()->comment('Trip attendant/host name');
            $table->string('host_phone')->nullable()->comment('Trip attendant/host phone');

            // Assignment metadata
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['trip_id', 'from_trip_stop_id', 'to_trip_stop_id']);
            $table->index('assigned_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bus_assignments');
    }
};
