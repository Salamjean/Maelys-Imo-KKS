<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendEmailToAgenceAfterRegistrationNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $code;
    public $email;
    public $logoUrl;
    public $codeId;

    public function __construct($codeToSend, $sendToemail, $codeId = null)
    {
        $this->code = $codeToSend;
        $this->email = $sendToemail;
        $this->logoUrl = asset('assets/images/mae-imo.png'); // URL du logo
        $this->codeId = $codeId;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Maelys-Imo : Votre agence est enregistré chez Maelys-Imo')
            ->from('contact@maelysimo.com', 'Maelys-Imo')
            ->view('emails.agence', [
                'code' => $this->code,
                'email' => $this->email,
                'logoUrl' => $this->logoUrl,
                'codeId' => $this->codeId,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
