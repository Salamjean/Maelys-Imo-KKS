<?php

namespace App\Mail;

use App\Models\Locataire;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CashVerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $locataire;
    public $code;

    public function __construct(Locataire $locataire, string $code)
    {
        $this->locataire = $locataire;
        $this->code = $code;
    }

    public function build()
    {
        return $this->subject('Votre code de vérification de paiement')
                    ->markdown('emails.cash_verification_code')
                    ->with([
                        'locataire' => $this->locataire,
                        'code' => $this->code,
                        'expiration' => now()->addHours(24)->format('d/m/Y à H:i')
                    ]);
    }
}