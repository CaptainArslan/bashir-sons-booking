<?php

use App\Enums\BusLayoutEnum;
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
        Schema::create('bus_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('total_rows')->nullable();
            $table->string('total_columns')->nullable();
            $table->string('total_seats')->nullable();
            $table->json('seat_map')->nullable();
            $table->string('status')->default(BusLayoutEnum::ACTIVE->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bus_layouts');
    }
};
