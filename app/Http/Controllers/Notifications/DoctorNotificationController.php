<?php

namespace App\Http\Controllers\Notifications;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;
class DoctorNotificationController extends Controller
{
    use ApiResponse;
    public function index(Request $request)
    {
        // Check if the doctor is authenticated
        if ($doctor = Auth::guard('doctor')->user()) {
            // Return the notifications for the authenticated user
            $notifications = $doctor->notifications()
            ->orderBy('created_at', 'desc') 
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'message' => $notification->data['message'],
                    'read_at' => $notification->read_at,
                ];
            });
            return $this->successData('Notifications fetched successfully', $notifications);
        } else {
            // Handle the case where the doctor is not authenticated
            return $this->error('Doctor not authenticated', 401);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Check if the doctor is authenticated
            if ($doctor = Auth::guard('doctor')->user()) {
                // Find the notification by ID for the authenticated doctor
                $notification = $doctor->notifications()->find($id);
                if ($notification) {
                    // Mark the notification as read
                    $notification->markAsRead();
                    return $this->success('Notification marked as read');
                } else {
                    // Return a 404 response indicating that the notification was not found
                    return $this->error('Notification not found', 404);
                }
            } else {
                // Handle the case where the doctor is not authenticated
                return $this->error('Doctor not authenticated', 401);
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
        // Check if the doctor is authenticated
        $doctor = Auth::guard('doctor')->user();
        if ($doctor) {
            // Retrieve all unread notifications for the authenticated doctor
            $unreadNotifications = $doctor->unreadNotifications;

            // Check if there are any unread notifications
            if ($unreadNotifications->isNotEmpty()) {
                // Loop through each unread notification and mark it as read
                foreach ($unreadNotifications as $notification) {
                    $notification->markAsRead();
                }

                return $this->success('Notifications marked as read');
            } else {
                // Return a message indicating no unread notifications
                return $this->sendData('No unread notifications to mark as read', null, 200);
            }
        } else {
            // Handle the case where the doctor is not authenticated
            return $this->error('Unauthorized', 401);
        }
    }
}