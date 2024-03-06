<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    protected $fillable =
    [
        'user_id',
        'doctor_id',
        'status',
        'time_id',
        'date_id'
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function time(): HasOne
    {
        return $this->hasOne(Time::class , 'id' , 'time_id');
    }

    public function date(): HasOne
    {
        return $this->hasOne(Date::class, 'id','date_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
