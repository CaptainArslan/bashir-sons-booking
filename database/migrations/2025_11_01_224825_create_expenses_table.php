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
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('expense_type'); // commission, ghakri, fuel, etc.
            $table->decimal('amount', 10, 2);
            $table->foreignId('from_terminal_id')->nullable()->constrained('terminals')->nullOnDelete();
            $table->foreignId('to_terminal_id')->nullable()->constrained('terminals')->nullOnDelete();
            $table->text('description')->nullable();
            $table->date('expense_date')->nullable();
            $table->timestamps();

            $table->index(['trip_id', 'expense_type']);
            $table->index('expense_date');
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
