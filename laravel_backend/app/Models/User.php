<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const INSTITUTIONAL_EMAIL_DOMAIN = 'lnu.edu.ph';

    public const INSTITUTIONAL_EMAIL_REGEX = '/^\d{1,7}@lnu\.edu\.ph$/i';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'student_id',
        'organization_type',
        'organization_name',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    // Relationships
    public function events()
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function approvedRegistrations()
    {
        return $this->hasMany(Registration::class, 'approved_by');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Role checking methods
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public static function isInstitutionalEmail(?string $email): bool
    {
        if (! is_string($email) || $email === '') {
            return false;
        }

        return preg_match(self::INSTITUTIONAL_EMAIL_REGEX, $email) === 1;
    }

    public function hasInstitutionalEmail(): bool
    {
        return self::isInstitutionalEmail($this->email);
    }
}
