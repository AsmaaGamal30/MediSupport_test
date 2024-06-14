<?php
namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    protected $fillable = [
        'type', 'notifiable_type', 'notifiable_id', 'data', 'online_booking_id', 'read_at'
    ];

    public function onlineBooking()
    {
        return $this->belongsTo(OnlineBooking::class, 'online_booking_id');
    }
}
