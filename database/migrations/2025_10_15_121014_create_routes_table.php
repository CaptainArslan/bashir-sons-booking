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
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            // Each route belongs to an operator/company (optional if multi-company)
            $table->foreignId('operator_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Code unique per operator (e.g., “KHI-HYD-01”)
            $table->string('code');
            $table->string('name'); // e.g., “Karachi → Hyderabad”

            $table->enum('direction', ['forward', 'return'])->default('forward');
            $table->foreignId('is_return_of')
                ->nullable()
                ->constrained('routes')
                ->nullOnDelete();

            $table->string('base_currency', 3)->default('PKR');
            $table->boolean('status')->default(true); // active/inactive

            $table->timestamps();

            $table->unique(['operator_id', 'code']); // unique code per operator
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
