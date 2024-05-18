<?php

namespace App\Http\Controllers\OnlineBooking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use App\Models\DoctorCash;
use App\Models\AdminCash;
use App\Models\OnlineBooking;
use App\Traits\ApiResponse;

class HandleStripeWebhooksController extends Controller
{
    use ApiResponse;
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $signature, $webhookSecret);
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    $bookingId = $paymentIntent->metadata->booking_id;
                    $doctorId = $paymentIntent->metadata->doctor_id;
                    $userId = $paymentIntent->metadata->user_id;

                    $booking = OnlineBooking::where('id', $bookingId)
                        ->where('status', 1)
                        ->where('user_id', $userId)
                        ->first();

                    if (!$booking) {
                        return $this->error('Booking not found or not eligible for completion', 404);
                    }

                    $booking->status = 2;
                    $booking->save();

                    break;
                default:
                    Log::warning('Unhandled webhook event type: ' . $event->type);
                    break;
            }
            return $this->success('Booking status updated successfully', 200);
        } catch (SignatureVerificationException $e) {
            Log::error('Webhook signature verification failed: ' . $e->getMessage());
            return $this->error('Signature verification failed', 403);
        } catch (\Exception $e) {
            Log::error('Error processing webhook: ' . $e->getMessage());
            return $this->error('Error processing webhook', 500);
        }
    }
}
