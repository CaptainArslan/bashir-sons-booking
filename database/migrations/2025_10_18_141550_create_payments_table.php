<?php

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('method')->default(PaymentMethodEnum::ONLINE->value);
            $table->string('online_method')->nullable();
            $table->string('status')->default(PaymentStatusEnum::PENDING->value);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('PKR');
            $table->string('transaction_id')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->string('transaction_status')->default(PaymentStatusEnum::PENDING->value);
            $table->timestamp('transaction_datetime')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['booking_id', 'transaction_id']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
