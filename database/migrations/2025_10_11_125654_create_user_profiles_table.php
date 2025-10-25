<?php

use App\Enums\GenderEnum;
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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('phone')->nullable();
            $table->string('cnic')->nullable();       // or 'b_form'
            $table->enum('gender', GenderEnum::getGenders())->default(GenderEnum::MALE->value);
            $table->text('notes')->nullable();
            $table->date('date_of_birth')->nullable();
            // $table->foreignId('terminal_id')->nullable()->constrained('terminals')->nullOnDelete(); // optional
            $table->text('address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
