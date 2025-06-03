<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordAgenceNotification extends Notification
{
    use Queueable;

    public $resetUrl;

    public function __construct($resetUrl)
    {
        $this->resetUrl = $resetUrl;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe')
            ->line('Cliquez sur le bouton ci-dessous pour réinitialiser votre mot de passe:')
            ->action('Réinitialiser le mot de passe', $this->resetUrl)
            ->line('Ce lien expirera dans 24 heures.')
            ->line('Si vous n\'avez pas demandé de réinitialisation, ignorez cet email.');
    }
}