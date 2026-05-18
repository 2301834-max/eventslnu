<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'organization',
        'description',
        'start_date',
        'end_date',
        'location',
        'max_participants',
        'status',
        'event_image',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'max_participants' => 'integer',
    ];

    protected $appends = [
        'poster_url',
        'event_image_url',
        'capacity',
        'is_registration_open',
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function approvedRegistrations()
    {
        return $this->registrations()->where('status', 'approved');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function qrCodes()
    {
        return $this->hasMany(QRCode::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', '!=', 'draft');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    public function scopeOngoing($query)
    {
        return $query->where('status', 'ongoing')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed')
            ->orWhere('end_date', '<', now());
    }

    // Methods
    public function getApprovedRegistrationsCount()
    {
        return $this->approvedRegistrations()->count();
    }

    public function getAttendanceCount()
    {
        return $this->attendanceRecords()->distinct('user_id')->count();
    }

    public function getAttendanceRate()
    {
        $approved = $this->getApprovedRegistrationsCount();
        if ($approved === 0) {
            return 0;
        }
        $attended = $this->getAttendanceCount();

        return round(($attended / $approved) * 100, 2);
    }

    public function isRegistrationFull()
    {
        if ($this->max_participants === 0) {
            return false; // unlimited
        }

        return $this->getApprovedRegistrationsCount() >= $this->max_participants;
    }

    public function getEventImageUrlAttribute(): ?string
    {
        if (! $this->poster) {
            return null;
        }

        if (filter_var($this->poster, FILTER_VALIDATE_URL)) {
            return $this->poster;
        }

        return asset('storage/'.ltrim($this->poster, '/'));
    }

    public function getPosterAttribute($value): ?string
    {
        return $this->attributes['event_image'] ?? null;
    }

    public function getPosterUrlAttribute(): ?string
    {
        if (! $this->poster) {
            return null;
        }

        if (filter_var($this->poster, FILTER_VALIDATE_URL)) {
            return $this->poster;
        }

        return route('event-posters.show', ['path' => ltrim($this->poster, '/')]);
    }

    public function getCapacityAttribute(): int
    {
        return $this->max_participants;
    }

    public function hasRegistrationClosed(): bool
    {
        return $this->end_date?->isPast() ?? false;
    }

    public function isOpenForRegistration(): bool
    {
        if (in_array($this->status, ['draft', 'cancelled', 'completed'], true)) {
            return false;
        }

        return ! $this->hasRegistrationClosed();
    }

    public function allowsAdminChanges(): bool
    {
        return in_array($this->status, ['draft', 'published'], true);
    }

    public function getIsRegistrationOpenAttribute(): bool
    {
        return $this->isOpenForRegistration();
    }
}
