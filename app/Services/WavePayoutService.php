<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WavePayoutService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.wave.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.wave.payout_api_key');
    }

    /**
     * Normalise un numéro CI vers le format international +225XXXXXXXXXX
     */
    private function normalizePhone(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // Déjà au format international complet (225 + 10 chiffres)
        if (str_starts_with($cleaned, '225') && strlen($cleaned) === 13) {
            return '+' . $cleaned;
        }

        // 10 chiffres locaux
        if (strlen($cleaned) === 10) {
            return '+225' . $cleaned;
        }

        // 8 chiffres (ancienne numérotation)
        if (strlen($cleaned) === 8) {
            return '+225' . $cleaned;
        }

        return '+225' . $cleaned;
    }

    /**
     * Envoyer un payout Wave vers un numéro mobile
     *
     * @return array ['success' => bool, 'data' => [...], 'error' => string]
     */
    public function sendPayout(string $telephone, int|float $montant, string $reference): array
    {
        try {
            $recipientPhone = $this->normalizePhone($telephone);

            $payload = [
                'currency'         => 'XOF',
                'receive_amount'   => (string) intval($montant),
                'mobile'           => $recipientPhone,
                'client_reference' => $reference,
                'name'             => 'Reversement Maelys-Imo',
            ];

            $response = Http::withToken($this->apiKey)
                ->withoutVerifying()
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->baseUrl}/payout", $payload);

            if ($response->successful()) {
                Log::info('Wave payout réussi', [
                    'reference' => $reference,
                    'telephone' => $recipientPhone,
                    'montant'   => $montant,
                    'data'      => $response->json(),
                ]);
                return ['success' => true, 'data' => $response->json()];
            }

            $errorBody = $response->json();
            Log::error('Wave payout erreur', [
                'status'    => $response->status(),
                'reference' => $reference,
                'response'  => $errorBody,
            ]);

            return [
                'success' => false,
                'error'   => $errorBody['message'] ?? 'Erreur lors du payout Wave.',
            ];
        } catch (\Exception $e) {
            Log::error('Wave payout exception', [
                'reference' => $reference,
                'message'   => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
