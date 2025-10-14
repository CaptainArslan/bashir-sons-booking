<?php

namespace App\Models;

use App\Enums\TerminalEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Terminal extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'name',
        'address',
        'phone',
        'email',
        'landmark',
        'latitude',
        'longitude',
        'status',
    ];

    protected $casts = [
        'city_id' => 'integer',
        'status' => TerminalEnum::class,
    ];

    // =============================
    // Relationships
    // =============================
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
