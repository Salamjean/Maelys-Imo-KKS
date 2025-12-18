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

        try {
            // Configuration stricte pour Android "Heads-up" Notification
            $androidConfig = AndroidConfig::fromArray([
                'priority' => 'high', // CRUCIAL pour flotter sur l'écran
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                    'sound' => 'default',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'channel_id' => 'channel_id_maelys_v3', // Doit matcher ton code Flutter
                    'default_sound' => true,
                    'default_vibrate_timings' => true,
                    'visibility' => 'public' // Affiche le contenu même sur l'écran de verrouillage
                ],
            ]);

            // Construction du message
            // Note : On n'utilise PAS 'Notification::create' ici pour éviter les conflits
            // On met tout dans AndroidConfig pour un contrôle total sur Android
            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withAndroidConfig($androidConfig)
                ->withData($data); // Les données cachées pour la redirection

            $this->messaging->send($message);
            return true;
        } catch (\Throwable $e) {
            Log::error("Erreur Firebase: " . $e->getMessage());
            return false;
        }
    }
}