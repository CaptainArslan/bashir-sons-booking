<?php

namespace App\Models;

use App\Enums\GenderEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    /** @use HasFactory<\Database\Factories\UserProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'cnic',
        'gender',
        'reference_id',
        'date_of_birth',
        'address',
    ];

    protected $casts = [
        'gender' => GenderEnum::class,
    ];

    // =============================
    // Relationships
    // =============================
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
