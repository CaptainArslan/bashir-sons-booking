<?php

use App\Enums\BusEnum;
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
        Schema::create('buses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->foreignId('bus_type_id')->constrained('bus_types');
            $table->foreignId('bus_layout_id')->constrained('bus_layouts');
            $table->string('registration_number');
            $table->string('model')->nullable();
            $table->string('color')->nullable();
            // $table->integer('total_seats')->default(0);
            $table->string('status')->default(BusEnum::ACTIVE->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buses');
    }
};
