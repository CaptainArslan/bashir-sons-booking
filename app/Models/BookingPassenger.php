<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\GenderEnum;

class BookingPassenger extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'name',
        'age',
        'gender',
        'cnic',
        'phone',
        'email',
    ];

    protected $casts = [
        'age' => 'integer',
        'gender' => GenderEnum::class,
    ];
}
