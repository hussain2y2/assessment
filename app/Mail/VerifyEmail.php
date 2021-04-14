<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $name;
    public $pin;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $pin)
    {
        $this->name = $name;
        $this->pin = $pin;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $name = $this->name;
        $pin = $this->pin;
        return $this->view('verify', compact('name', 'pin'));
    }
}
