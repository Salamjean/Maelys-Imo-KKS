<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendEmailToCommercialAfterRegistrationNotification extends Notification
{
    use Queueable;

    public $code;
    public $email;
    public $logoUrl;
    public $codeId;

    public function __construct($codeToSend, $sendToemail, $codeId = null)
    {
        $this->code = $codeToSend;
        $this->email = $sendToemail;
        $this->logoUrl = asset('assets/images/mae-imo.png');
        $this->codeId = $codeId;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Commercial : Vous avez été enregistré sur Maelys-Imo')
            ->from('contact@maelysimo.com', 'Maelys-Imo')
            ->view('emails.commercial', [
                'code' => $this->code,
                'email' => $this->email,
                'logoUrl' => $this->logoUrl,
                'codeId' => $this->codeId,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
