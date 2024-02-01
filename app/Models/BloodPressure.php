<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodPressure extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'pressure_advice_id',
        'systolic',
        'diastolic',
    ];

    public function pressureAdvice()
    {
        return $this->belongsTo(PressureAdvice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
