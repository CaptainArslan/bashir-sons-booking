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
        Schema::create('route_stop_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained('route_timetables')->cascadeOnDelete();
            $table->foreignId('route_stop_id')->constrained('route_stops')->cascadeOnDelete();
            $table->integer('sequence');
            $table->time('arrival_time')->nullable();
            $table->time('departure_time')->nullable();
            $table->boolean('allow_online_booking')->default(true);
            $table->timestamps();

            $table->unique(['timetable_id', 'route_stop_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_stop_times');
    }
};
