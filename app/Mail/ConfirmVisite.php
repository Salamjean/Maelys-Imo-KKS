<?php

namespace App\Mail;

use App\Models\Bien;
use App\Models\Visite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmVisite extends Mailable
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
        return $this->subject('Votre demande de visite a été confirmée')
                    ->from('no-reply@example.com', 'Maelys-Imo')
                    ->view('emails.confirmVisite');
    }
}