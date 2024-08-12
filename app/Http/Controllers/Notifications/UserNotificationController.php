<?php

namespace App\Http\Controllers\Notifications;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;
use App\Services\Notification\UserNotificationService;

class UserNotificationController extends Controller
{
    use ApiResponse;

    protected $notificationService;

    public function __construct(UserNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        try {
            // Check if the user is authenticated
            if ($user = Auth::guard('user')->user()) {
                // Fetch notifications using the service
                $notifications = $this->notificationService->getNotifications($user);

                return $this->successData('Notifications fetched successfully', $notifications);
            } else {
                // Handle the case where the user is not authenticated
                return $this->error('User not authenticated', 401);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching notifications: ' . $e->getMessage());
            return $this->error('Internal Server Error', 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Check if the user is authenticated
            if ($user = Auth::guard('user')->user()) {
                // Update the notification status using the service
                $result = $this->notificationService->markNotificationAsRead($user, $id);

                return $result ? $this->success('Notification marked as read')
                    : $this->error('Notification not found', 404);
            } else {
                // Handle the case where the user is not authenticated
                return $this->error('User not authenticated', 401);
            }
        } catch (\Exception $e) {
            Log::error('Error updating notification: ' . $e->getMessage());
            return $this->error('Internal Server Error', 500);
        }
    }

    public function markAsRead(Request $request)
    {
        try {
            // Check if the user is authenticated
            if ($user = Auth::guard('user')->user()) {
                // Mark all unread notifications as read using the service
                $result = $this->notificationService->markAllNotificationsAsRead($user);

                return $result ? $this->success('Notifications marked as read')
                    : $this->success('No unread notifications to mark as read');
            } else {
                // Handle the case where the user is not authenticated
                return $this->error('Unauthorized', 401);
            }
        } catch (\Exception $e) {
            Log::error('Error marking notifications as read: ' . $e->getMessage());
            return $this->error('Internal Server Error', 500);
        }
    }
}
