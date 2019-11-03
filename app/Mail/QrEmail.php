<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QrEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $qr;

    /**
     * Create a new message instance.
     *
     * @param $qr
     */
    public function __construct($qr)
    {
        $this->qr = $qr;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->view('emails.qr')->subject("Este es tu qr.");

        $email->attach(public_path($this->qr));

        return $this->view('emails.qr')->subject("Este es tu qr.");
    }
}
