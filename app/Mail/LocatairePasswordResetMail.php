<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LocatairePasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $resetLink;

    public function __construct($resetLink)
    {
        $this->resetLink = $resetLink;
    }

    public function build()
    {
        return $this->subject('Réinitialisation de votre mot de passe')
                    ->from('contact@maelysimo.com', 'Maelys-Imo')
                    ->view('emails.password-reset')
                    ->with(['resetLink' => $this->resetLink]);
    }
}