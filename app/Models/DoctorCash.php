<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorCash extends Model
{
    use HasFactory;
    protected $fillable = ['doctor_id', 'online_booking_id', 'total'];


    public function online_booking()
    {
        return $this->belongsTo(OnlineBooking::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
