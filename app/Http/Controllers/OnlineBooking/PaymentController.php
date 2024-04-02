<?php

namespace App\Http\Controllers\OnlineBooking;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AdminCash;
use App\Models\DoctorCash;
use App\Models\OnlineBooking;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Customer;
use Stripe\PaymentMethod;

class PaymentController extends Controller
{
    use ApiResponse;

    public function makePayment($bookingId, Request $request)
    {
        try {
            $onlineBooking = OnlineBooking::find($bookingId);

            if (!$onlineBooking) {
                return $this->error('No available online booking with this ID.', 404);
            }

            if ($onlineBooking->status !== 'accepted') {
                return $this->error('The booking is not in an accepted status for payment. Please wait until your doctor accepts your booking request.', 422);
            }

            $user = auth()->guard('user')->user();

            Stripe::setApiKey(env('STRIPE_SECRET'));

            // Check if the user has a Stripe customer ID
            if (!$user->stripe_customer_id) {
                // If not, create a new customer in Stripe and associate it with the user
                $customer = Customer::create([
                    'email' => $user->email,
                    'name' => $user->name . ' ' . $user->last_name,
                ]);
                $user->stripe_customer_id = $customer->id;
                $user->save();
            }
            $paymentMethod = $this->createPaymentMethod($request->token);
            $paymentMethod->attach(['customer' => $user->stripe_customer_id]);


            // Retrieve the related doctor
            $doctor = $onlineBooking->doctor;

            if (!$doctor) {
                return $this->error('No doctor associated with this booking.', 404);
            }

            $booking_price = $doctor->price * 100;
            $admin_amount = $booking_price * 0.1;
            $doctor_amount = $booking_price - $admin_amount;

            // Create a payment intent
            $paymentIntent = PaymentIntent::create([
                'amount' =>  $booking_price,
                'currency' => 'usd',
                'description' => 'Payment for Doctor ' . $doctor->first_name . ' ' . $doctor->last_name . ' booking',
                'payment_method' => $paymentMethod->id,
                //'confirmation_method' => 'manual', // Use manual confirmation
                'confirm' => true,
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never', // Disable redirects
                ],
                'statement_descriptor' => 'Booking Payment',
                'metadata' => [
                    'booking_id' => $onlineBooking->id,
                    'doctor_id' => $doctor->id,
                ],
                'customer' => $user->stripe_customer_id,
            ]);

            return response()->json([
                'response' => [
                    'id' =>  $paymentIntent->id,
                    'client_secret' => $paymentIntent->client_secret,
                    'amount' =>  $paymentIntent->amount,
                    'customer' =>  $paymentIntent->customer,
                    'payment_method' => $paymentIntent->payment_method,
                    'description' =>  $paymentIntent->description,
                    'metadata' =>  $paymentIntent->metadata,
                    'statement_descriptor' => $paymentIntent->statement_descriptor,
                    'user_id' => $onlineBooking->user_id,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function createPaymentMethod($token)
    {
        return PaymentMethod::create([
            'type' => 'card',
            'card' => [
                'token' => $token,
            ],

        ]);
    }
}
