<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AnnouncementUser extends Pivot
{
    protected $table = 'announcement_user';

    protected $fillable = [
        'announcement_id',
        'user_id',
        'read_at',
        'dismissed',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'dismissed' => 'boolean',
    ];

    // =============================
    // Relationships
    // =============================
    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
