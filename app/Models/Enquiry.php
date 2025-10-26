<?php

namespace App\Models;

use App\Enums\EnquiryStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    /** @use HasFactory<\Database\Factories\EnquiryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'service',
        'status',
    ];

    protected $casts = [
        'name' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'message' => 'string',
        'service' => 'string',
        'status' => EnquiryStatusEnum::class,
    ];
}
