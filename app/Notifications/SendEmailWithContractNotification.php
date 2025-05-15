<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendEmailWithContractNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $prenom;
    public $pdfPath;

    public function __construct($prenom, $pdfPath)
    {
        $this->prenom = $prenom;
        $this->pdfPath = $pdfPath;
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
        ->subject('Votre Contrat de Location')
        ->line("Bonjour {$this->prenom},")
        ->line('Veuillez trouver ci-joint votre contrat de location.')
        ->attach(storage_path('app/' . $this->pdfPath), [
            'as' => 'contrat_location.pdf',
            'mime' => 'application/pdf',
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
