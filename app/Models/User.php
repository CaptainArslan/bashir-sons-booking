<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    use HasRoles;

    const DEFAULT_ROLES = [
        'Super Admin',
        'Admin',
        'Customer',
        'Employee',
    ];

    const DEFAULT_PERMISSIONS = [
        'access admin panel',
        'manage users',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'terminal_id',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            // 'two_factor_secret' => 'encrypted',
            // 'two_factor_recovery_codes' => 'encrypted',
            // 'two_factor_confirmed_at' => 'datetime',
        ];
    }

    // =============================
    // Two Factor Authentication
    // =============================
    public function hasTwoFactorEnabled(): bool
    {
        return ! is_null($this->two_factor_secret);
    }

    // =============================
    // Two Factor Authentication Helper Methods
    // =============================
    public function enableTwoFactorAuthentication(string $secret, array $recoveryCodes): void
    {
        $this->update([
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($recoveryCodes)),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function disableTwoFactorAuthentication(): void
    {
        $this->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }

    // =============================
    // Relationships
    // =============================
    public function profile(): HasOne
    {
        return $this->hasOne(profile::class);
    }

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class);
    }

    public function routes(): BelongsToMany
    {
        return $this->belongsToMany(Route::class, 'route_user')
            ->withTimestamps();
    }

    public function employeeRoutes(): BelongsToMany
    {
        return $this->belongsToMany(Route::class, 'employee_routes')
            ->withPivot(['can_book'])
            ->withTimestamps();
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function counterBookings()
    {
        return $this->hasMany(Booking::class, 'booked_by_user_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'incurred_by');
    }

    public function announcements(): BelongsToMany
    {
        return $this->belongsToMany(Announcement::class, 'announcement_user')
            ->withPivot(['read_at', 'dismissed'])
            ->withTimestamps();
    }

    public function unreadAnnouncements(): BelongsToMany
    {
        return $this->announcements()
            ->wherePivotNull('read_at')
            ->wherePivot('dismissed', false)
            ->orderByPivot('created_at', 'desc');
    }

    public function readAnnouncements(): BelongsToMany
    {
        return $this->announcements()
            ->wherePivotNotNull('read_at')
            ->orderByPivot('read_at', 'desc');
    }

    public function dismissedAnnouncements(): BelongsToMany
    {
        return $this->announcements()
            ->wherePivot('dismissed', true)
            ->orderByPivot('created_at', 'desc');
    }

    public function pinnedAnnouncements(): BelongsToMany
    {
        return $this->announcements()
            ->wherePivot('is_pinned', true)
            ->orderByPivot('created_at', 'desc');
    }

    public function featuredAnnouncements(): BelongsToMany
    {
        return $this->announcements()
            ->wherePivot('is_featured', true)
            ->orderByPivot('created_at', 'desc');
    }

    // =============================
    // Helper Methods
    // =============================
    public function isEmployee(): bool
    {
        return $this->hasRole('Employee');
    }

    public function getTerminalNameAttribute(): string
    {
        return $this->terminal?->name ?? 'No Terminal';
    }

    public function getTerminalCodeAttribute(): string
    {
        return $this->terminal?->code ?? 'N/A';
    }
}
