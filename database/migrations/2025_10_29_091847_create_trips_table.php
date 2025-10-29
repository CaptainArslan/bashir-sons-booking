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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained('timetables')->nullOnDelete();
            $table->foreignId('route_id')->constrained('routes')->cascadeOnDelete();
            $table->foreignId('bus_id')->nullable()->constrained('buses')->nullOnDelete();

            $table->date('departure_date');
            $table->timestamp('departure_datetime');
            $table->timestamp('estimated_arrival_datetime')->nullable();

            $table->string('status')->default('pending');
            $table->text('notes')->nullable();

            // Driver info (inline, not a separate table)
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->string('driver_license')->nullable();
            $table->string('driver_cnic')->nullable();
            $table->text('driver_address')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['bus_id', 'departure_datetime']);
            $table->index(['departure_date', 'status']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
