<?php

namespace App\Services\OnlineBooking;

use App\Models\OnlineBooking;
use App\Models\User;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Customer;
use App\Traits\ApiResponse;

class PaymentService
{
    use ApiResponse;

    public function makePayment($bookingId, $request)
    {
        $onlineBooking = OnlineBooking::find($bookingId);

        if (!$onlineBooking) {
            return $this->error('No available online booking with this ID.', 404);
        }

        if ($onlineBooking->status !== 1) {
            return $this->error('The booking is not in an accepted status for payment. Please wait until your doctor accepts your booking request.', 422);
        }

        $user = auth()->guard('user')->user();

        Stripe::setApiKey(env('STRIPE_SECRET'));

        if (!$user->stripe_customer_id) {
            $customer = Customer::create([
                'email' => $user->email,
                'name' => $user->name . ' ' . $user->last_name,
            ]);
            $user->stripe_customer_id = $customer->id;
            $user->save();
        }

        $doctor = $onlineBooking->doctor;

        if (!$doctor) {
            return $this->error('No doctor associated with this booking.', 404);
        }

        $booking_price = $doctor->price * 100;

        $paymentIntent = PaymentIntent::create([
            'amount' =>  $booking_price,
            'currency' => 'usd',
            'description' => 'Payment for Doctor ' . $doctor->first_name . ' ' . $doctor->last_name . ' booking',
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never',
            ],
            'statement_descriptor' => 'Booking Payment',
            'metadata' => [
                'booking_id' => $onlineBooking->id,
                'doctor_id' => $doctor->id,
                'user_id' => $user->id,
            ],
            'customer' => $user->stripe_customer_id,
        ]);

        return response()->json([
            'response' => [
                'id' =>  $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,
                'customer' =>  $paymentIntent->customer,
                'metadata' =>  $paymentIntent->metadata,
            ],
        ]);
    }
}
