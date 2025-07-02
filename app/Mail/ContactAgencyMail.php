<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactAgencyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $content;
    public $userName;
    public $userEmail;
    public $bien;

    public function __construct($subject, $content, $userName, $userEmail, $bien)
    {
        $this->subject = $subject;
        $this->content = $content;
        $this->userName = $userName;
        $this->userEmail = $userEmail;
        $this->bien = $bien;
    }

    public function build()
    {
        return $this->from('no-reply@maelysimo.com', 'Maelys-Imo') // Email d'envoi fixe
                    ->subject($this->subject)
                    ->replyTo($this->userEmail, $this->userName) // Adresse de réponse du locataire
                    ->view('emails.contact_agency')
                    ->with([
                        'content' => $this->content,
                        'userName' => $this->userName,
                        'userEmail' => $this->userEmail,
                        'subject' => $this->subject,
                        'bien' => $this->bien,
                    ]);
    }
}