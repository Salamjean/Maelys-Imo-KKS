<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WaveService
{
    protected string $apiKey;
    protected string $webhookSecret;
    protected string $baseUrl = 'https://api.wave.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.wave.api_key');
        $this->webhookSecret = config('services.wave.webhook_secret');
    }

    /**
     * Créer une session de paiement Wave Checkout
     */
    public function createCheckoutSession(array $params): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->withoutVerifying()
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->baseUrl}/checkout/sessions", [
                    'amount'           => (string) $params['amount'],
                    'currency'         => $params['currency'] ?? 'XOF',
                    'error_url'        => $params['error_url'],
                    'success_url'      => $params['success_url'],
                    'client_reference' => $params['client_reference'] ?? null,
                ]);

            if ($response->successful()) {
                Log::info('Wave session créée', ['data' => $response->json()]);
                return ['success' => true, 'data' => $response->json()];
            }

            Log::error('Wave checkout error', [
                'status'   => $response->status(),
                'response' => $response->body(),
            ]);
            return [
                'success' => false,
                'error'   => $response->json()['message'] ?? 'Erreur lors de la création du paiement Wave.',
            ];
        } catch (\Exception $e) {
            Log::error('Wave exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Vérifier la signature du webhook Wave
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $expected = hash_hmac('sha256', $payload, $this->webhookSecret);
        return hash_equals($expected, $signature);
    }
}
