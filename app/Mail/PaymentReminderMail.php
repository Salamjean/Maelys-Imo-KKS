<?php

namespace App\Mail;

use App\Models\Locataire;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $locataire;
    public $nouveauMontant;
    public $tauxMajoration;

    public function __construct($locataire, $nouveauMontant, $tauxMajoration)
    {
        $this->locataire = $locataire;
        $this->nouveauMontant = $nouveauMontant;
        $this->tauxMajoration = $tauxMajoration;
    }

    public function build()
    {
        return $this->subject('Rappel de paiement de loyer')
                    ->from('contact@maelysimo.com', 'Maelys-Imo')
                    ->view('emails.payment_reminder')
                    ->with([
                        'locataire' => $this->locataire,
                        'nouveauMontant' => $this->nouveauMontant,
                        'tauxMajoration' => $this->tauxMajoration,
                        'montantOriginal' => $this->locataire->bien->prix
                    ]);
    }
}