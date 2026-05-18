<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'event_id',
        'user_id',
        'checked_in_at',
        'checked_out_at',
        'qr_code_reference',
        'check_in_location',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function qrCode()
    {
        return $this->belongsTo(QRCode::class, 'qr_code_reference', 'code');
    }

    // Methods
    public function checkout()
    {
        $this->checked_out_at = now();

        return $this->save();
    }

    public function getDurationInMinutes()
    {
        if (! $this->checked_out_at) {
            return null;
        }

        return $this->checked_in_at->diffInMinutes($this->checked_out_at);
    }
}
