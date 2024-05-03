<?php

namespace App\Http\Controllers\Notifications;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;

class UserNotificationController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        // Check if the user is authenticated
        if ($user = Auth::guard('user')->user()) {
            // Return the notifications for the authenticated user
            $notifications = $user->notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'message' => $notification->data['message'],
                    'read_at' => $notification->read_at,
                ];
            });

            return $this->successData('Notifications fetched successfully', $notifications);
        } else {
            // Handle the case where the user is not authenticated
            return $this->error('User not authenticated', 401);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Check if the user is authenticated
            if ($user = Auth::guard('user')->user()) {
                // Find the notification by ID for the authenticated user
                $notification = $user->notifications()->find($id);
                if ($notification) {
                    // Mark the notification as read
                    $notification->markAsRead();
                    return $this->success('Notification marked as read');
                } else {
                    // Return a 404 response indicating that the notification was not found
                    return $this->error('Notification not found', 404);
                }
            } else {
                // Handle the case where the user is not authenticated
                return $this->error('User not authenticated', 401);
            }
        } catch (\Exception $e) {
            // Log the exception for debugging
            Log::error('Error updating notification: ' . $e->getMessage());
            // Return an error response
            return $this->error('Internal Server Error', 500);
        }
    }

    public function markAsRead(Request $request)
    {
        // Check if the user is authenticated
        $user = Auth::guard('user')->user();
        if ($user) {
            // Retrieve all unread notifications for the authenticated user
            $unreadNotifications = $user->unreadNotifications;

            // Check if there are any unread notifications
            if ($unreadNotifications->isNotEmpty()) {
                // Mark all unread notifications as read
                $user->unreadNotifications->markAsRead();

                return $this->success('Notifications marked as read');
            } else {
                // Return a message indicating no unread notifications
                return $this->success('No unread notifications to mark as read');
            }
        } else {
            // Handle the case where the user is not authenticated
            return $this->error('Unauthorized', 401);
        }
    }
}
