<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ChequeVerificationCode extends Mailable
{
    use Queueable, SerializesModels;

    public $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function build()
    {
        return $this->subject('Code de vérification pour votre paiement par chèque')
                    ->from('contact@maelysimo.com', 'Maelys-Imo')
                    ->view('emails.cheque_verification_code');
    }
}