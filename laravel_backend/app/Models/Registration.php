<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_id',
        'user_id',
        'status',
        'registration_number',
        'remarks',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function attendanceRecord()
    {
        return $this->hasOne(AttendanceRecord::class);
    }

    public function qrCode()
    {
        return $this->hasOne(QRCode::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Methods
    public function approve($adminId, $remarks = null)
    {
        $this->status = 'approved';
        $this->approved_by = $adminId;
        $this->approved_at = now();
        $this->remarks = $remarks;
        return $this->save();
    }

    public function reject($adminId, $remarks)
    {
        $this->status = 'rejected';
        $this->approved_by = $adminId;
        $this->remarks = $remarks;
        return $this->save();
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($registration) {
            $registration->registration_number = 'REG-' . date('YmdHis') . '-' . rand(1000, 9999);
        });
    }
}
