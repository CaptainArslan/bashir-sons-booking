<?php

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
        Schema::create('bus_facility', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bus_id')->constrained('buses')->cascadeOnDelete();
            $table->foreignId('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['bus_id', 'facility_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bus_facility');
    }
};
