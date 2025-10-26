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
        Schema::create('seat_locks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->cascadeOnDelete();

            $table->string('seat_id');
            $table->string('seat_number');
            $table->string('seat_row');
            $table->string('seat_column');

            $table->string('lock_type')->default('temporary'); // temporary, phone_hold

            $table->timestamp('locked_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('released_at')->nullable();

            $table->text('metadata')->nullable(); // JSON data for session info

            $table->timestamps();

            $table->index(['trip_id', 'seat_id']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seat_locks');
    }
};
