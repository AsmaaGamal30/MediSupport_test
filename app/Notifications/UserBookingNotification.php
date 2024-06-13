<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserBookingNotification extends Notification
{
    use Queueable;

    protected $message;
    protected $types;

    public function __construct($message, $types = [])
    {
        $this->message = $message;
        $this->types = $types;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        // Add types to the notification array
        $notificationData = [
            'message' => $this->message,
        ];

        // Merge types if provided
        if (!empty($this->types)) {
            $notificationData['types'] = $this->types;
        }

        return $notificationData;
    }
}
