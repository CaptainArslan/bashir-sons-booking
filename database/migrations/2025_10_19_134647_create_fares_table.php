<?php

use App\Enums\FareStatusEnum;
use App\Enums\DiscountTypeEnum;
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
        Schema::create('fares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_terminal_id')->constrained('terminals')->onDelete('cascade');
            $table->foreignId('to_terminal_id')->constrained('terminals')->onDelete('cascade');
            $table->unsignedBigInteger('base_fare');
            $table->string('discount_type')->nullable();
            $table->unsignedBigInteger('discount_value')->default(0);
            $table->unsignedBigInteger('final_fare')->default(0);
            $table->string('currency')->default('PKR');
            $table->string('status')->default(FareStatusEnum::ACTIVE->value);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['from_terminal_id', 'to_terminal_id']);
            $table->index('status');

            // Ensure no duplicate fares for same terminal pair
            $table->unique(['from_terminal_id', 'to_terminal_id'], 'unique_terminal_pair');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fares');
    }
};
