<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    /** @use HasFactory<\Database\Factories\GeneralSettingFactory> */
    use HasFactory;

    protected $fillable = [
        'company_name',
        'email',
        'phone',
        'alternate_phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'website_url',
        'logo',
        'favicon',
        'tagline',
        'facebook_url',
        'instagram_url',
        'twitter_url',
        'linkedin_url',
        'youtube_url',
        'support_email',
        'support_phone',
        'business_hours',
        'advance_booking_enable',
        'advance_booking_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'advance_booking_enable' => 'boolean',
            'advance_booking_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
