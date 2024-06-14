<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlineBooking extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'doctor_id', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function doctorCash()
    {
        return $this->hasOne(DoctorCash::class);
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'online_booking_id');
    }

}