<?php

use App\Enums\FrequencyTypeEnum;
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
        Schema::create('route_timetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')
                ->constrained('routes')
                ->cascadeOnDelete();
            $table->string('trip_code')->unique()->comment('Unique identifier for each scheduled trip');
            $table->time('departure_time')->comment('Departure time from the first terminal');
            $table->time('arrival_time')->nullable()->comment('Expected arrival time at the last terminal');
            $table->string('frequency')->default(FrequencyTypeEnum::DAILY->value);
            $table->json('operating_days')->nullable()->comment('If frequency is "custom", array of operating days like ["monday", "wednesday"]');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_timetables');
    }
};
