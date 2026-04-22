<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendEmailToProprietaireAfterRegistrationNotification extends Notification
{
    use Queueable;

    public $code;
    public $email;
    public $logoUrl;
    public $codeId;

    /**
     * Create a new notification instance.
     */
    public function __construct($codeToSend, $sendToemail, $codeId = null)
    {
        $this->code = $codeToSend;
        $this->email = $sendToemail;
        $this->logoUrl = asset('assets/images/mae-imo.png');
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
            ->subject('Maelys-Imo : Bienvenue chez Maelys-Imo')
            ->from('contact@maelysimo.com', 'Maelys-Imo')
            ->view('emails.proprietaire', [
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
