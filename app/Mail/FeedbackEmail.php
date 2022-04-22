<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FeedbackEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $address = $this->data['address'];
        $subject = $this->data['subject'];
        $from = $this->data['from'];
        $message = $this->data['message'];
        Log::debug($message);
        // $name = 'Jane Doe';

        Log::debug("building email");

        return $this->view('emails/feedback')
                    ->from($address)
                    ->cc($address)
                    // ->bcc($address, $name)
                    ->replyTo($address)
                    ->subject($subject)
                    ->with([ 'body' => $message, 'email' => $from ]);
    }
}
