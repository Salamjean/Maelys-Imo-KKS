<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VisitUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function build()
    {
        return $this->subject($this->details['subject'])
                    ->from('contact@maelysimo.com', 'Maelys-Imo')
                    ->view('emails.visit_updated');
    }
}