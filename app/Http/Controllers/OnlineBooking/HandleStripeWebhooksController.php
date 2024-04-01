<?php

namespace App\Http\Controllers\OnlineBooking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use App\Models\DoctorCash;
use App\Models\AdminCash;
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
                    $amount = $paymentIntent->amount / 100;
                    $adminAmount = $amount * 0.1;
                    $doctorAmount = $amount - $adminAmount;
                    DoctorCash::create([
                        'doctor_id' => $doctorId,
                        'booking_id' => $bookingId,
                        'total' => $doctorAmount,
                    ]);
                    AdminCash::create([
                        'admin_id' => 1,
                        'doctor_id' => $doctorId,
                        'booking_id' => $bookingId,
                        'total' => $adminAmount,
                    ]);
                    break;
                default:
                    Log::warning('Unhandled webhook event type: ' . $event->type);
                    break;
            }
            return $this->success('Webhook event processed successfully');
        } catch (SignatureVerificationException $e) {
            Log::error('Webhook signature verification failed: ' . $e->getMessage());
            return $this->error('Signature verification failed', 403);
        } catch (\Exception $e) {
            Log::error('Error processing webhook: ' . $e->getMessage());
            return $this->error('Error processing webhook', 500);
        }
    }
}
