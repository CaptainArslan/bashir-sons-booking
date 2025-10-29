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
            $table->enum('created_by_type', ['admin', 'employee', 'customer'])->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('booked_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('terminal_id')->nullable()->constrained('terminals')->nullOnDelete();
            $table->foreignId('from_stop_id')->constrained('route_stops')->cascadeOnDelete();
            $table->foreignId('to_stop_id')->constrained('route_stops')->cascadeOnDelete();

            $table->enum('channel', ['online', 'counter', 'phone'])->default('online');
            $table->enum('status', ['hold', 'confirmed', 'cancelled', 'expired'])->default('hold');

            $table->timestamp('reserved_until')->nullable();
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded', 'failed'])->default('unpaid');
            $table->enum('payment_method', ['cash', 'card', 'mobile_wallet', 'bank_transfer', 'other'])->nullable();
            $table->string('online_transaction_id')->nullable();

            $table->decimal('total_fare', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2);
            $table->string('currency', 3)->default('PKR');

            $table->integer('total_passengers')->default(1);
            $table->text('notes')->nullable();

            // how much payment received and returned after deduction from customer
            $table->decimal('payment_received_from_customer', 10, 2)->default(0);
            $table->decimal('return_after_deduction_from_customer', 10, 2)->default(0);

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
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
