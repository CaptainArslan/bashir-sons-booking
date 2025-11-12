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
        Schema::table('buses', function (Blueprint $table) {
            $table->integer('total_seats')->nullable()->after('color');
        });

        // Make bus_layout_id nullable (need to drop and recreate foreign key)
        Schema::table('buses', function (Blueprint $table) {
            $table->dropForeign(['bus_layout_id']);
            $table->foreignId('bus_layout_id')->nullable()->change();
            $table->foreign('bus_layout_id')->references('id')->on('bus_layouts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buses', function (Blueprint $table) {
            $table->dropColumn('total_seats');
            $table->dropForeign(['bus_layout_id']);
            $table->foreignId('bus_layout_id')->nullable(false)->change();
            $table->foreign('bus_layout_id')->references('id')->on('bus_layouts');
        });
    }
};
