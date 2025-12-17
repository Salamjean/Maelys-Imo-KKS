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
        $credentialsPath = base_path(env('FIREBASE_CREDENTIALS'));
        
        $options = HttpClientOptions::default()->withGuzzleConfigOptions([
            'verify' => storage_path('app/certs/cacert.pem'),
        ]);

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