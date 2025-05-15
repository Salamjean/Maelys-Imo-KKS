<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendEmailToLocataireAfterRegistrationNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $code;
    public $email;
    public $agenceName;
    public $logoUrl;

    public function __construct($codeToSend, $sendToemail, $agenceName)
    {
        $this->code = $codeToSend;
        $this->email = $sendToemail;
        $this->agenceName = $agenceName;
        $this->logoUrl = asset('assets/images/kkstevhno.jpeg'); // URL du logo
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
            ->subject($this->agenceName . ' : Vous êtes enregistré comme locataire') 
            ->from('no-reply@example.com', $this->agenceName)
            ->view('emails.locataire', [
                'code' => $this->code,
                'agenceName' => $this->agenceName,
                'locataire' => $notifiable,
                'email' => $this->email,
                'logoUrl' => $this->logoUrl,
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
