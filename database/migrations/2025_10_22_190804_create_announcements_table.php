<?php

use App\Enums\AnnouncementAudienceTypeEnum;
use App\Enums\AnnouncementDisplayTypeEnum;
use App\Enums\AnnouncementPriorityEnum;
use App\Enums\AnnouncementStatusEnum;
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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('image')->nullable();
            $table->string('link')->nullable();
            $table->string('status')->default(AnnouncementStatusEnum::ACTIVE->value);
            $table->string('display_type')->default(AnnouncementDisplayTypeEnum::BANNER->value); // UI type
            $table->string('priority')->default(AnnouncementPriorityEnum::MEDIUM->value); // to order banners
            $table->string('audience_type')->default(AnnouncementAudienceTypeEnum::ALL->value);
            $table->json('audience_payload')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
