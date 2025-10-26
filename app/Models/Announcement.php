<?php

namespace App\Models;

use App\Enums\AnnouncementAudienceTypeEnum;
use App\Enums\AnnouncementDisplayTypeEnum;
use App\Enums\AnnouncementPriorityEnum;
use App\Enums\AnnouncementStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Announcement extends Model
{
    /** @use HasFactory<\Database\Factories\AnnouncementFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image',
        'link',
        'status',
        'display_type',
        'priority',
        'audience_type',
        'audience_payload',
        'start_date',
        'end_date',
        'is_pinned',
        'is_featured',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'audience_payload' => 'array',
        'status' => AnnouncementStatusEnum::class,
        'display_type' => AnnouncementDisplayTypeEnum::class,
        'priority' => AnnouncementPriorityEnum::class,
        'audience_type' => AnnouncementAudienceTypeEnum::class,
        'is_pinned' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    // =============================
    // Relationships
    // =============================
    public function readers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'announcement_user')
            ->withPivot('read_at', 'dismissed')
            ->withTimestamps();
    }

    // =============================
    // Scopes
    // =============================
    public function scopeActive($q)
    {
        return $q->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            });
    }

    // =============================
    // Helper Methods
    // =============================
    public function targetsUser(User $user): bool
    {
        if ($this->audience_type === AnnouncementAudienceTypeEnum::ALL->value) {
            return true;
        }

        return match ($this->audience_type) {
            AnnouncementAudienceTypeEnum::USERS->value => $this->readers()->where('user_id', $user->id)->exists(),
            AnnouncementAudienceTypeEnum::ROLES->value => $user->hasAnyRole($this->audience_payload),
            default => true,
        };
    }
}
