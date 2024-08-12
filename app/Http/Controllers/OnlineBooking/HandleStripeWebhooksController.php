<?php

namespace App\Http\Controllers\OnlineBooking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use App\Traits\ApiResponse;
use App\Services\OnlineBooking\StripeWebhookService;

class HandleStripeWebhooksController extends Controller
{
    use ApiResponse;

    protected $stripeWebhookService;

    public function __construct(StripeWebhookService $stripeWebhookService)
    {
        $this->stripeWebhookService = $stripeWebhookService;
    }

    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = $this->stripeWebhookService->constructEvent($payload, $signature, $webhookSecret);
            $this->stripeWebhookService->handleEvent($event);

            return $this->success('Webhook handled successfully', 200);
        } catch (SignatureVerificationException $e) {
            Log::error('Webhook signature verification failed: ' . $e->getMessage());
            return $this->error('Signature verification failed', 403);
        } catch (\Exception $e) {
            Log::error('Error processing webhook: ' . $e->getMessage());
            return $this->error('Error processing webhook', 500);
        }
    }
}
