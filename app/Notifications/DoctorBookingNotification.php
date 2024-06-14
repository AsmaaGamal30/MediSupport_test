<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DoctorBookingNotification extends Notification
{
    use Queueable;

    protected $message;
    protected $types;
    protected $onlineBookingId;

    public function __construct($message, $types = [], $onlineBookingId = null)
    {
        $this->message = $message;
        $this->types = $types;
        $this->onlineBookingId = $onlineBookingId;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
            'types' => $this->types,
            'online_booking_id' => $this->onlineBookingId,
        ];
    }
}
