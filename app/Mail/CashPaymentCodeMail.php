<?php

namespace App\Mail;

use App\Models\Locataire;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CashPaymentCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code;
    public $locataire;
    public $montant;

    public function __construct($code, Locataire $locataire, $montant = null)
    {
        $this->montant = $montant;
        $this->code = $code;
        $this->locataire = $locataire;
    }

    public function build()
    {
        return $this->subject('[URGENT] Votre code de paiement en espèces')
               ->markdown('emails.cash_payment_code')
               ->with([
                   'code' => $this->code,
                   'locataire' => $this->locataire,
                     'montant' => $this->montant,
                   'expiration' => now()->addHours(24)->format('d/m/Y à H:i')
               ]);
    }
}