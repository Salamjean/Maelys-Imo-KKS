<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Http\HttpClientOptions;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\AndroidConfig;
class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        $credentialsFile = env('FIREBASE_CREDENTIALS');

        if (!$credentialsFile) {
            Log::error('Firebase: FIREBASE_CREDENTIALS non défini dans .env');
            return; // Ou lancer une exception
        }

        $credentialsPath = base_path($credentialsFile);

        // Options HTTP (SSL) SEULEMENT si le certificat local existe (utile en local, pas forcément en prod)
        $certPath = storage_path('app/certs/cacert.pem');
        $options = HttpClientOptions::default();
        
        if (file_exists($certPath)) {
            $options = $options->withGuzzleConfigOptions([
                'verify' => $certPath,
            ]);
        }

        $factory = (new Factory)
            ->withServiceAccount($credentialsPath)
            ->withHttpClientOptions($options);
            
        $this->messaging = $factory->createMessaging();
    }

  // Modification : Ajout du paramètre $imageUrl
    public function sendNotification($fcmToken, $title, $body, $data = [], $imageUrl = null)
    {
        if (!$fcmToken) return false;

        try {
            // Configuration Android (Son + Priorité + Icône par défaut)
            $androidConfig = AndroidConfig::fromArray([
                'priority' => 'high',
                'notification' => [
                    'sound' => 'default',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'channel_id' => 'channel_id_maelys_v2',
                    'icon' => '@mipmap/ic_launcher' // Icône locale (petite)
                ],
            ]);

            // Création de la notification (Titre, Corps, Image URL)
            $notification = Notification::create($title, $body, $imageUrl);

            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withNotification($notification)
                ->withAndroidConfig($androidConfig)
                ->withData($data);

            $this->messaging->send($message);
            return true;
        } catch (\Throwable $e) {
            Log::error("Erreur Firebase: " . $e->getMessage());
            return false;
        }
    }

}