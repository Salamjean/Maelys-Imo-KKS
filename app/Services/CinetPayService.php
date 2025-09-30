<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class CinetPayService
{
    private $apiKey;
    private $siteId;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.cinetpay.api_key');
        $this->siteId = config('services.cinetpay.site_id');
        $this->baseUrl = config('services.cinetpay.base_url', 'https://api-checkout.cinetpay.com/v2/payment');
    }

    public function initializePayment(array $paymentData)
    {
        try {
            Log::info('Initialisation paiement CinetPay', $paymentData);

            $payload = [
                "apikey" => $this->apiKey,
                "site_id" => $this->siteId,
                "transaction_id" => $paymentData['transaction_id'],
                "amount" => $paymentData['amount'],
                "currency" => "XOF",
                "description" => $paymentData['description'],
                "customer_id" => $paymentData['customer_id'],
                "customer_name" => $paymentData['customer_name'],
                "customer_surname" => $paymentData['customer_surname'],
                "customer_email" => $paymentData['customer_email'],
                "customer_phone_number" => $paymentData['customer_phone_number'],
                "customer_address" => $paymentData['customer_address'] ?? "",
                "customer_city" => $paymentData['customer_city'] ?? "",
                "customer_country" => $paymentData['customer_country'] ?? "CI",
                "notify_url" => route('api.cinetpay.notify'),
                "return_url" => route('api.cinetpay.return'),
                "channels" => "ALL",
                "metadata" => $paymentData['metadata'] ?? "",
                "lang" => "FR",
            ];

            $client = new \GuzzleHttp\Client([
                'verify' => false,
                'timeout' => 30,
            ]);

            $response = $client->post($this->baseUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload
            ]);

            $responseData = json_decode($response->getBody(), true);

            Log::info('Réponse initialisation CinetPay', $responseData);

            if ($response->getStatusCode() === 200) {
                if ($responseData['code'] === '201') {
                    return [
                        'success' => true,
                        'payment_url' => $responseData['data']['payment_url'],
                        'payment_token' => $responseData['data']['payment_token'],
                        'transaction_id' => $paymentData['transaction_id']
                    ];
                } else {
                    Log::error('Erreur CinetPay: ' . ($responseData['message'] ?? 'Unknown error'));
                    return [
                        'success' => false,
                        'error' => $responseData['message'] ?? 'Erreur inconnue de CinetPay',
                        'code' => $responseData['code'] ?? 'unknown'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'error' => 'Erreur HTTP: ' . $response->getStatusCode()
                ];
            }

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('Exception Request CinetPay: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erreur de connexion à CinetPay: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            Log::error('Exception CinetPay: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function checkPaymentStatus($transactionId)
    {
        try {
            Log::info('Vérification statut CinetPay', ['transaction_id' => $transactionId]);

            $payload = [
                "apikey" => $this->apiKey,
                "site_id" => $this->siteId,
                "transaction_id" => $transactionId
            ];

            $client = new \GuzzleHttp\Client([
                'verify' => false,
                'timeout' => 30,
            ]);

            $response = $client->post('https://api-checkout.cinetpay.com/v2/payment/check', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload
            ]);

            $responseData = json_decode($response->getBody(), true);

            Log::info('Réponse vérification statut CinetPay', $responseData);

            return $responseData;

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('Erreur requête vérification statut CinetPay: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erreur de connexion à CinetPay'
            ];
        } catch (\Exception $e) {
            Log::error('Erreur vérification statut CinetPay: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Vérifier la signature CinetPay pour sécurité
     */
    public function verifySignature($data, $signature)
    {
        try {
            $apiKey = $this->apiKey;
            $siteId = $this->siteId;
            
            // Construction de la chaîne pour la signature selon la documentation CinetPay
            $signatureData = $data['cpm_trans_id'] . $siteId . $apiKey;
            $computedSignature = hash('sha256', $signatureData);
            
            $isValid = hash_equals($computedSignature, $signature);
            
            Log::info('Vérification signature', [
                'signature_reçue' => $signature,
                'signature_calculée' => $computedSignature,
                'is_valid' => $isValid
            ]);
            
            return $isValid;

        } catch (\Exception $e) {
            Log::error('Erreur vérification signature', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            return false;
        }
    }

    /**
     * Annuler un paiement
     */
    public function cancelPayment($transactionId)
    {
        try {
            Log::info('Annulation paiement CinetPay', ['transaction_id' => $transactionId]);

            $payload = [
                "apikey" => $this->apiKey,
                "site_id" => $this->siteId,
                "transaction_id" => $transactionId
            ];

            $client = new \GuzzleHttp\Client([
                'verify' => false,
                'timeout' => 30,
            ]);

            $response = $client->post('https://api-checkout.cinetpay.com/v2/payment/cancel', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload
            ]);

            $responseData = json_decode($response->getBody(), true);

            Log::info('Réponse annulation CinetPay', $responseData);

            return $responseData;

        } catch (\Exception $e) {
            Log::error('Erreur annulation paiement CinetPay: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}