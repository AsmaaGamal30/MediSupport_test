<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Time extends Model
{
    use HasFactory;

    protected $fillable = [
        'time',
        'date_id',
        'doctor_id'
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
    
    public function date(): BelongsTo
    {
        return $this->belongsTo(Date::class);
    }
    
    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class);
    }
}
