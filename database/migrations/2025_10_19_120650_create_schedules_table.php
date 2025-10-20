<?php

use App\Enums\FrequencyTypeEnum;
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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('route_id')->constrained('routes')->cascadeOnDelete();
            $table->string('code')->unique()->comment('Unique identifier for each schedule');
            $table->time('departure_time')->comment('Departure time from the first terminal');
            $table->time('arrival_time')->comment('Arrival time at the last terminal');
            $table->string('frequency')->default(FrequencyTypeEnum::DAILY->value);
            $table->json('operating_days')->nullable()->comment('If frequency is "custom", array of operating days like ["monday", "wednesday"]');
            $table->boolean('is_active')->default(true);
            $table->integer('seat_hold_duration')->default(10)->comment('Duration in minutes to hold the seats');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
