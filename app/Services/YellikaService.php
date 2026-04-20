<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YellikaService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected string $senderId;

    public function __construct()
    {
        $this->apiUrl   = rtrim(config('services.yellika.api_url'), '/');
        $this->apiKey   = config('services.yellika.api_key');
        $this->senderId = config('services.yellika.sender_id');
    }

    /**
     * Normalise un numéro de téléphone au format international sans le signe +.
     * Exemple : 0798278981 → 2250798278981
     *           +2250798278981 → 2250798278981
     *           2250798278981 → 2250798278981 (inchangé)
     *
     * @param  string  $phone
     * @param  string  $countryCode  Indicatif pays sans + (ex: "225" pour la Côte d'Ivoire)
     * @return string
     */
    protected function normalizePhone(string $phone, string $countryCode = '225'): string
    {
        // Supprimer tout ce qui n'est pas un chiffre ou un +
        $phone = preg_replace('/[^\d+]/', '', $phone);

        // Retirer le + initial si présent
        $phone = ltrim($phone, '+');

        // Si le numéro commence par l'indicatif pays, le laisser tel quel
        if (str_starts_with($phone, $countryCode)) {
            return $phone;
        }

        // Si le numéro commence par 0, préfixer avec l'indicatif pays en conservant le 0
        if (str_starts_with($phone, '0')) {
            return $countryCode . $phone;
        }

        // Sinon, préfixer directement avec l'indicatif pays
        return $countryCode . $phone;
    }

    /**
     * Envoie un SMS via l'API Yellika (1smsafrica).
     *
     * @param  string  $to      Numéro de téléphone du destinataire
     * @param  string  $message Contenu du SMS
     * @return bool
     */
    public function send(string $to, string $message): bool
    {
        $to = $this->normalizePhone($to);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept'        => 'application/json',
            ])->post($this->apiUrl . '/sms/send', [
                'recipient' => $to,
                'sender_id' => $this->senderId,
                'message'   => $message,
                'type'      => 'plain',
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['status']) && $data['status'] === 'success') {
                Log::info('YellikaService: SMS envoyé avec succès', [
                    'to'       => $to,
                    'response' => $data,
                ]);
                return true;
            }

            Log::warning('YellikaService: Réponse inattendue de l\'API', [
                'to'       => $to,
                'status'   => $response->status(),
                'response' => $data,
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error('YellikaService: Erreur lors de l\'envoi du SMS', [
                'to'    => $to,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
