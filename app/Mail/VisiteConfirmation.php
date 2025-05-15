<?php

namespace App\Mail;

use App\Models\Bien;
use App\Models\Visite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VisiteConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $visite;
    public $bien;

    public function __construct(Visite $visite, Bien $bien)
    {
        $this->visite = $visite;
        $this->bien = $bien;
    }

    public function build()
    {
        return $this->subject('Confirmation de votre demande de visite')
                    ->from('no-reply@example.com', 'Maelys-Imo')
                    ->view('emails.visite_confirmation');
    }
}