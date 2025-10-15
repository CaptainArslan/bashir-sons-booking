<?php

use App\Enums\DiscountTypeEnum;
use App\Enums\RouteFareStatusEnum;
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
        Schema::create('route_fares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('routes')->onDelete('cascade');
            $table->foreignId('from_stop_id')->constrained('route_stops')->onDelete('cascade');
            $table->foreignId('to_stop_id')->constrained('route_stops')->onDelete('cascade');
            $table->decimal('base_fare', 10, 2)->comment('Base fare amount in PKR');
            $table->enum('discount_type', DiscountTypeEnum::getStatuses())->nullable()->comment('Type of discount applied');
            $table->decimal('discount_value', 10, 2)->nullable()->comment('Discount amount or percentage');
            $table->decimal('final_fare', 10, 2)->comment('Final fare after discount');
            $table->string('status')->default(RouteFareStatusEnum::ACTIVE->value);
            $table->timestamps();

            // Indexes for better performance
            $table->index(['route_id', 'status']);
            $table->index(['from_stop_id', 'to_stop_id']);
            $table->index('status');

            // Unique constraint to prevent duplicate fares for same route and stops
            $table->unique(['route_id', 'from_stop_id', 'to_stop_id'], 'unique_route_fare');

            // // Check constraint to ensure from_stop_id != to_stop_id
            // $table->check('from_stop_id != to_stop_id');

            // // Check constraint to ensure final_fare >= 0
            // $table->check('final_fare >= 0');

            // // Check constraint to ensure base_fare > 0
            // $table->check('base_fare > 0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_fares');
    }
};
