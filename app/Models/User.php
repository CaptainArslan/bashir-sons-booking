<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Crypt;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
}
