<?php

use App\Enums\TripInstanceStatusEnum;
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
        Schema::create('trip_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_timetable_id')->constrained('route_timetables');
            $table->foreignId('assigned_bus_id')->nullable()->constrained('buses');
            $table->foreignId('assigned_driver_id')->nullable()->constrained('users');
            $table->date('departure_date');
            $table->time('planned_departure_time');
            $table->time('planned_arrival_time');
            $table->time('actual_departure_time')->nullable();
            $table->time('actual_arrival_time')->nullable();
            $table->string('status')->default(TripInstanceStatusEnum::PENDING->value);
            $table->string('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_instances');
    }
};
