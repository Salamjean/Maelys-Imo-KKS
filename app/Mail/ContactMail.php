<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
         return $this->from('contact@maelysimo.com', $this->data['name'] . ' (via formulaire)')
                ->replyTo($this->data['email'], $this->data['name'])
                ->subject('Nouveau message de contact: ' . $this->data['subject'])
                ->view('emails.contact')
                ->with(['data' => $this->data]);
    }
}