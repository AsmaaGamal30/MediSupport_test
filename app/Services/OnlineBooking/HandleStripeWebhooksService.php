<?php

namespace App\Services\OnlineBooking;

use Stripe\Webhook;
use Illuminate\Support\Facades\Log;
use App\Models\OnlineBooking;

class StripeWebhookService
{
    public function constructEvent($payload, $signature, $webhookSecret)
    {
        return Webhook::constructEvent($payload, $signature, $webhookSecret);
    }

    public function handleEvent($event)
    {
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;

            default:
                Log::warning('Unhandled webhook event type: ' . $event->type);
                break;
        }
    }

    protected function handlePaymentIntentSucceeded($paymentIntent)
    {
        $bookingId = $paymentIntent->metadata->booking_id;
        $userId = $paymentIntent->metadata->user_id;

        $booking = OnlineBooking::where('id', $bookingId)
            ->where('status', 1)
            ->where('user_id', $userId)
            ->first();

        if (!$booking) {
            throw new \Exception('Booking not found or not eligible for completion');
        }

        $booking->status = 2;
        $booking->save();
    }
}