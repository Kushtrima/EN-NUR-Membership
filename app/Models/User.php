<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'date_of_birth',
        'address',
        'postal_code',
        'city',
        'marital_status',
        'phone_number',
        'email',
        'password',
        'role',
        'terms_accepted_at',
        'terms_version',
        'terms_accepted_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'terms_accepted_at' => 'datetime',
            'date_of_birth' => 'date',
            'password' => 'hashed',
        ];
    }

    /**
     * Role constants
     */
    const ROLE_USER = 'user';
    const ROLE_ADMIN = 'admin';
    const ROLE_SUPER_ADMIN = 'super_admin';

    /**
     * Check if user is admin (includes super admin).
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN]);
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Check if user is regular user.
     */
    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    /**
     * Get all available roles.
     */
    public static function getRoles(): array
    {
        return [
            self::ROLE_USER => 'User',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_SUPER_ADMIN => 'Super Admin',
        ];
    }

    /**
     * Get user's payments.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get user's membership renewals.
     */
    public function membershipRenewals()
    {
        return $this->hasMany(MembershipRenewal::class);
    }

    /**
     * Check if user has accepted terms and conditions.
     */
    public function hasAcceptedTerms(): bool
    {
        return !is_null($this->terms_accepted_at);
    }

    /**
     * Accept terms and conditions.
     */
    public function acceptTerms(string $version = '1.0', ?string $ipAddress = null): void
    {
        $this->update([
            'terms_accepted_at' => now(),
            'terms_version' => $version,
            'terms_accepted_ip' => $ipAddress ?? request()->ip(),
        ]);
    }

    /**
     * Check if user is fully verified (email + terms).
     */
    public function isFullyVerified(): bool
    {
        return $this->hasVerifiedEmail() && $this->hasAcceptedTerms();
    }
} 