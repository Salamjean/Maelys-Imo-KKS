<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        // On initialise Firebase avec le fichier JSON défini dans le .env
        $credentialsPath = base_path(env('FIREBASE_CREDENTIALS'));
        
        $factory = (new Factory)->withServiceAccount($credentialsPath);
        $this->messaging = $factory->createMessaging();
    }

    public function sendNotification($fcmToken, $title, $body, $data = [])
    {
        if (!$fcmToken) {
            return false;
        }

        try {
            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withNotification(Notification::create($title, $body))
                ->withData($data); // Données sup (ex: ID du bien, type de notif)

            $this->messaging->send($message);
            return true;
        } catch (\Throwable $e) {
            // Log l'erreur pour le débogage
            \Log::error("Erreur Firebase: " . $e->getMessage());
            return false;
        }
    }
}