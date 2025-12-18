<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Http\HttpClientOptions;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        $credentialsFile = env('FIREBASE_CREDENTIALS');

        if (!$credentialsFile) {
            Log::error('Firebase: FIREBASE_CREDENTIALS non défini dans .env');
            return;
        }

        $credentialsPath = base_path($credentialsFile);

        // Correction SSL pour WAMP/Local (Force le certificat)
        $certPath = storage_path('app/certs/cacert.pem');
        if (file_exists($certPath)) {
             putenv("CURL_CA_BUNDLE=$certPath");
             putenv("SSL_CERT_FILE=$certPath");
        }

        // Initialisation standard
        $factory = (new Factory)->withServiceAccount($credentialsPath);
        $this->messaging = $factory->createMessaging();
    }

    // On a retiré $imageUrl et simplifié la logique pour le Heads-up
   public function sendNotification($fcmToken, $title, $body, $data = [])
    {
        if (!$fcmToken) return false;

        // 1. On s'assure que les data sont des Strings (Firebase n'aime pas les entiers)
        $cleanData = array_map('strval', $data);

        try {
            $androidConfig = AndroidConfig::fromArray([
                'priority' => 'high',
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                    'sound' => 'default',
                    // CORRECTION 1 : On passe à v4 pour réinitialiser les réglages du téléphone
                    'channel_id' => 'channel_id_maelys_v4', 
                    // CORRECTION 2 : On force le nom de ton icône (sans l'extension)
                    'icon' => 'ic_notification', 
                    'default_sound' => true,
                    'default_vibrate_timings' => true,
                    'visibility' => 'public',
                ],
            ]);

            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withAndroidConfig($androidConfig)
                ->withData($cleanData); // Données pour la redirection

            $this->messaging->send($message);
            return true;
        } catch (\Throwable $e) {
            Log::error("Erreur Firebase: " . $e->getMessage());
            return false;
        }
    }
}