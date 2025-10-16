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
        Schema::table('route_stops', function (Blueprint $table) {
            // Change approx_travel_time from time to integer (minutes)
            $table->unsignedInteger('approx_travel_time')->nullable()->change()->after('distance_from_previous');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('route_stops', function (Blueprint $table) {
            // Revert back to time type
            $table->unsignedInteger('approx_travel_time')->nullable()->change()->after('distance_from_previous');
        });
    }
};
