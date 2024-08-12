<?php

namespace App\Services\Notification;

class DoctorNotificationService
{
    public function getNotifications($doctor)
    {
        return $doctor->notifications()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'message' => $notification->data['message'],
                    'type' => $notification->data['types'] ?? null,
                    'online_booking_id' => $notification->data['online_booking_id'] ?? null,
                    'read_at' => $notification->read_at,
                ];
            });
    }

    public function markNotificationAsRead($doctor, $notificationId)
    {
        $notification = $doctor->notifications()->find($notificationId);
        if ($notification) {
            $notification->markAsRead();
            return true;
        }
        return false;
    }

    public function markAllNotificationsAsRead($doctor)
    {
        $unreadNotifications = $doctor->unreadNotifications;
        if ($unreadNotifications->isNotEmpty()) {
            foreach ($unreadNotifications as $notification) {
                $notification->markAsRead();
            }
            return true;
        }
        return false;
    }
}
