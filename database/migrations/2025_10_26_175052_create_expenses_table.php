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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->cascadeOnDelete();

            $table->string('type'); // fuel, toll, driver_pay, maintenance, misc

            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('PKR');

            $table->text('description')->nullable();
            $table->foreignId('incurred_by')->nullable()->constrained('users')->nullOnDelete();

            $table->date('incurred_date')->nullable();
            $table->text('receipt_number')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['trip_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
