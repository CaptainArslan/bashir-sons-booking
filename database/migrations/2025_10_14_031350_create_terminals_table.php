<?php

use App\Enums\TerminalEnum;
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
        Schema::create('terminals', function (Blueprint $table) {
            $table->id();

            // Make sure cities.id is BIGINT UNSIGNED
            $table->foreignId('city_id')
                ->nullable()
                ->constrained('cities')
                ->nullOnDelete();

            $table->string('name');
            $table->string('code')->nullable();
            $table->string('address');
            $table->string('phone');
            $table->string('email')->nullable();

            $table->string('landmark')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();

            $table->string('status')->default(TerminalEnum::ACTIVE->value);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['city_id', 'name']);
            $table->index(['city_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terminals');
    }
};
