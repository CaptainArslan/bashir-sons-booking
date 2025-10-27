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
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('reserved_until')->nullable()->after('cancelled_at');
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'refunded', 'failed'])->default('pending')->after('reserved_until');
            $table->enum('payment_method', ['cash', 'card', 'mobile_wallet', 'bank_transfer', 'other'])->nullable()->after('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['reserved_until', 'payment_status', 'payment_method']);
        });
    }
};
