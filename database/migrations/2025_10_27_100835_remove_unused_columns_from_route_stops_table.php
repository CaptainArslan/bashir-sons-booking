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
            $table->dropColumn([
                'distance_from_previous',
                'approx_travel_time',
                'is_pickup_allowed',
                'is_dropoff_allowed',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('route_stops', function (Blueprint $table) {
            $table->decimal('distance_from_previous', 8, 2)->nullable();
            $table->integer('approx_travel_time')->default(0);
            $table->boolean('is_pickup_allowed')->default(true);
            $table->boolean('is_dropoff_allowed')->default(true);
        });
    }
};
