<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvitationEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $name;
    public $invitation_link;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $invitation_link)
    {
        $this->name = $name;
        $this->invitation_link = $invitation_link;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $name = $this->name;
        $invitation_link = $this->invitation_link;
        return $this->view('mail', compact('name', 'invitation_link'));
    }
}
