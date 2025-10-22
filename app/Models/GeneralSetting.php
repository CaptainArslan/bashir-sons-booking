<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
    ];

    protected $casts = [
        'advance_booking_enable' => 'boolean',
    ];
}
