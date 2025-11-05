<?php

namespace App\Models;

use App\Enums\GenderEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    /** @use HasFactory<\Database\Factories\UserProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'cnic',
        'gender',
        'notes',
        'date_of_birth',
        'address',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'gender' => GenderEnum::class,
        ];
    }

    // =============================
    // Relationships
    // =============================
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
