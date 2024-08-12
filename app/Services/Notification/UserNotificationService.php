<?php

namespace App\Services\Notification;

class UserNotificationService
{
    public function getNotifications($user)
    {
        return $user->notifications()
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

    public function markNotificationAsRead($user, $notificationId)
    {
        $notification = $user->notifications()->find($notificationId);
        if ($notification) {
            $notification->markAsRead();
            return true;
        }
        return false;
    }

    public function markAllNotificationsAsRead($user)
    {
        $unreadNotifications = $user->unreadNotifications;
        if ($unreadNotifications->isNotEmpty()) {
            $unreadNotifications->markAsRead();
            return true;
        }
        return false;
    }
}
