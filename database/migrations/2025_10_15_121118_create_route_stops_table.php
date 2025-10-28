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
        Schema::create('route_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('routes')->cascadeOnDelete();
            $table->foreignId('terminal_id')->constrained('terminals')->cascadeOnDelete();
            $table->unsignedInteger('sequence'); // order of stop in route (1, 2, 3...)
            $table->timestamps();
            $table->unique(['route_id', 'sequence']); // ensures unique sequence per route
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_stops');
    }
};
