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
            $table->time('arrival_time')->nullable()->after('approx_travel_time');
            $table->time('departure_time')->nullable()->after('arrival_time');
            $table->boolean('is_online_booking_allowed')->default(true)->after('departure_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('route_stops', function (Blueprint $table) {
            $table->dropColumn('arrival_time');
            $table->dropColumn('departure_time');
            $table->dropColumn('is_online_booking_allowed');
        });
    }
};
