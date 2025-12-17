<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Http\HttpClientOptions;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

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

    public function sendNotification($fcmToken, $title, $body, $data = [])
    {
        if (!$fcmToken) return false;

        try {
            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            $this->messaging->send($message);
            return true;
        } catch (\Throwable $e) {
            Log::error("Erreur Firebase: " . $e->getMessage());
            return false;
        }
    }
}