<?php

use App\Enums\DiscountTypeEnum;
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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();

            $table->string('title'); // short label like "Weekend Offer"
            $table->foreignId('route_id')->constrained('routes')->cascadeOnDelete();

            $table->string('discount_type')->default(DiscountTypeEnum::PERCENT->value);
            $table->decimal('value', 10, 2); // discount value

            // Platform flags
            $table->boolean('is_android')->default(true);
            $table->boolean('is_ios')->default(true);
            $table->boolean('is_web')->default(true);
            $table->boolean('is_counter')->default(true);

            // Active window
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            // Optional time restriction within the day
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
