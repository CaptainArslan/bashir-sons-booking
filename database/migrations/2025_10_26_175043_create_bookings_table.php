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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique();

            $table->foreignId('trip_id')->constrained('trips')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('booked_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('from_stop_id')->constrained('route_stops')->cascadeOnDelete();
            $table->foreignId('to_stop_id')->constrained('route_stops')->cascadeOnDelete();

            $table->string('type'); // online, counter, phone
            $table->string('status')->default('pending');

            $table->decimal('total_fare', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2);
            $table->string('currency', 3)->default('PKR');

            $table->integer('total_passengers')->default(1);

            $table->text('passenger_contact_phone')->nullable();
            $table->text('passenger_contact_email')->nullable();
            $table->text('passenger_contact_name')->nullable();

            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['trip_id', 'status']);
            $table->index(['user_id']);
            $table->index(['booking_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
