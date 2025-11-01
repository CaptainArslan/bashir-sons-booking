<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'trip_id',
        'from_trip_stop_id',
        'to_trip_stop_id',
        'bus_id',
        'driver_name',
        'driver_phone',
        'driver_cnic',
        'driver_license',
        'driver_address',
        'host_name',
        'host_phone',
        'assigned_by_user_id',
        'assigned_at',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    // =============================
    // Relationships
    // =============================
    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function fromTripStop(): BelongsTo
    {
        return $this->belongsTo(TripStop::class, 'from_trip_stop_id');
    }

    public function toTripStop(): BelongsTo
    {
        return $this->belongsTo(TripStop::class, 'to_trip_stop_id');
    }

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    // =============================
    // Accessors
    // =============================
    public function getFromTerminalNameAttribute(): string
    {
        return $this->fromTripStop?->terminal?->name ?? 'N/A';
    }

    public function getFromTerminalCodeAttribute(): string
    {
        return $this->fromTripStop?->terminal?->code ?? 'N/A';
    }

    public function getToTerminalNameAttribute(): string
    {
        return $this->toTripStop?->terminal?->name ?? 'N/A';
    }

    public function getToTerminalCodeAttribute(): string
    {
        return $this->toTripStop?->terminal?->code ?? 'N/A';
    }

    public function getSegmentLabelAttribute(): string
    {
        return sprintf(
            '%s → %s',
            $this->from_terminal_code,
            $this->to_terminal_code
        );
    }
}
