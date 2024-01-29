<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;
    public $verificationCode;

     /**
     * Create a new message instance.
     *
     * @param  int  $verificationCode
     * @return void
     */

    public function __construct($verificationCode)
    {
        $this->verificationCode = $verificationCode;


    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Verification Code for Password Reset')
                    ->view('emails.reset_password');
    }
}