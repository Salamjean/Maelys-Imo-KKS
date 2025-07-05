<?php

namespace App\Mail;

use App\Models\Bien;
use App\Models\Visite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DoneVisite extends Mailable
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
        return $this->subject('Votre demande de visite a été effectuée')
                    ->from('contact@maelysimo.com', 'Maelys-Imo')
                    ->view('emails.doneVisite');
    }
}