<?php
// app/Services/CinetPayService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CinetPayService
{
    private $apiKey;
    private $siteId;
    private $baseUrl;
    private $notifyUrl;
    private $returnUrl;

    public function __construct()
    {
        $this->apiKey = config('services.cinetpay.api_key');
        $this->siteId = config('services.cinetpay.site_id');
        $this->baseUrl = config('services.cinetpay.base_url', 'https://api-checkout.cinetpay.com/v2/payment');
        $this->notifyUrl = route('api.cinetpay.notify');
        $this->returnUrl = route('api.cinetpay.return');
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
                "alternative_currency" => "",
                "description" => $paymentData['description'],
                "customer_id" => $paymentData['customer_id'],
                "customer_name" => $paymentData['customer_name'],
                "customer_surname" => $paymentData['customer_surname'],
                "customer_email" => $paymentData['customer_email'],
                "customer_phone_number" => $paymentData['customer_phone_number'],
                "customer_address" => $paymentData['customer_address'] ?? "",
                "customer_city" => $paymentData['customer_city'] ?? "",
                "customer_country" => $paymentData['customer_country'] ?? "CI",
                "customer_state" => $paymentData['customer_state'] ?? "",
                "customer_zip_code" => $paymentData['customer_zip_code'] ?? "",
                "notify_url" => $this->notifyUrl,
                "return_url" => $this->returnUrl,
                "channels" => "ALL",
                "metadata" => $paymentData['metadata'] ?? "",
                "lang" => "FR",
                "invoice_data" => [
                    "Donnee1" => "",
                    "Donnee2" => "",
                    "Donnee3" => ""
                ]
            ];

            Log::info('Payload CinetPay', $payload);

            // SOLUTION: Désactiver la vérification SSL
            $client = new \GuzzleHttp\Client([
                'verify' => false, // Désactive la vérification SSL
                'timeout' => 30,
                'connect_timeout' => 10,
            ]);

            $response = $client->post($this->baseUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload
            ]);

            $responseData = json_decode($response->getBody(), true);

            Log::info('Réponse CinetPay', $responseData);

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
                        'error' => $responseData['message'] ?? 'Erreur inconnue de CinetPay'
                    ];
                }
            } else {
                Log::error('Erreur HTTP CinetPay: ' . $response->getStatusCode());
                return [
                    'success' => false,
                    'error' => 'Erreur de connexion à CinetPay: ' . $response->getStatusCode()
                ];
            }

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('Erreur Guzzle CinetPay: ' . $e->getMessage());
            
            if ($e->hasResponse()) {
                $errorResponse = $e->getResponse();
                $errorBody = $errorResponse->getBody()->getContents();
                Log::error('Response body: ' . $errorBody);
            }
            
            return [
                'success' => false,
                'error' => 'Erreur de connexion: ' . $e->getMessage()
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
            $payload = [
                "apikey" => $this->apiKey,
                "site_id" => $this->siteId,
                "transaction_id" => $transactionId
            ];

            $client = new \GuzzleHttp\Client([
                'verify' => false, // Désactive aussi la vérification SSL pour les checks
                'timeout' => 30,
            ]);

            $response = $client->post('https://api-checkout.cinetpay.com/v2/payment/check', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload
            ]);

            return json_decode($response->getBody(), true);

        } catch (\Exception $e) {
            Log::error('Erreur vérification statut CinetPay: ' . $e->getMessage());
            return null;
        }
    }
}