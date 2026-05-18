<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QRCode extends Model
{
    use HasFactory;

    protected $table = 'qr_codes';

    protected $fillable = [
        'registration_id',
        'event_id',
        'code',
        'status',
        'type',
        'expires_at',
        'scanned_at',
        'qr_image_data',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'scanned_at' => 'datetime',
        'generated_at' => 'datetime',
    ];

    // Relationships
    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function attendanceRecord()
    {
        return $this->hasOne(AttendanceRecord::class, 'qr_code_reference', 'code');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeScanned($query)
    {
        return $query->where('status', 'scanned');
    }

    // Methods
    public function markAsScanned()
    {
        $this->status = 'scanned';
        $this->scanned_at = now();

        return $this->save();
    }

    public function isExpired()
    {
        if (! $this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    public function isActive()
    {
        return $this->status === 'active' && ! $this->isExpired();
    }

    public function revoke()
    {
        $this->status = 'revoked';

        return $this->save();
    }
}
