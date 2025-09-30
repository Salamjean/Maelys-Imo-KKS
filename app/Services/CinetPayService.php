<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class CinetPayService
{
    private $apiKey;
    private $siteId;
    private $baseUrl;
    private $mode;

    public function __construct()
    {
        $this->apiKey = config('services.cinetpay.api_key');
        $this->siteId = config('services.cinetpay.site_id');
        $this->mode = config('services.cinetpay.mode', 'PRODUCTION');
        $this->baseUrl = 'https://api-checkout.cinetpay.com/v2';
    }

    public function initializePayment(array $paymentData)
    {
        Log::info('🚀 DÉBUT Initialisation Paiement CinetPay', [
            'transaction_id' => $paymentData['transaction_id'],
            'amount' => $paymentData['amount'],
            'mode' => $this->mode
        ]);

        try {
            // URLs de callback
            $basePublicUrl = config('app.url');
            $notifyUrl = $basePublicUrl . '/api/cinetpay/notify';
            $returnUrl = $basePublicUrl . '/api/cinetpay/return';

            Log::info('📋 URLs de callback configurées', [
                'notify_url' => $notifyUrl,
                'return_url' => $returnUrl,
                'base_url' => $basePublicUrl
            ]);

            // Construction du payload
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
                "customer_phone_number" => $this->formatPhoneNumber($paymentData['customer_phone_number']),
                "customer_address" => $paymentData['customer_address'] ?? "Non spécifiée",
                "customer_city" => $paymentData['customer_city'] ?? "Abidjan",
                "customer_country" => "CI",
                "customer_zip_code" => "00225",
                "notify_url" => $notifyUrl,
                "return_url" => $returnUrl,
                "channels" => "ALL",
                "metadata" => $paymentData['metadata'] ?? "",
                "lang" => "fr",
            ];

            Log::info('📦 Payload CinetPay préparé', [
                'transaction_id' => $payload['transaction_id'],
                'amount' => $payload['amount'],
                'customer_phone' => $payload['customer_phone_number']
            ]);

            // ✅ SOLUTION TEMPORAIRE : Désactiver la vérification SSL
            $client = new \GuzzleHttp\Client([
                'timeout' => 30,
                'verify' => false, // ← Désactive la vérification SSL
                'http_errors' => false,
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false
                ]
            ]);

            Log::info('🌐 Envoi requête à CinetPay (SSL désactivé)');

            $response = $client->post($this->baseUrl . '/payment', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $payload
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);

            Log::info('📨 Réponse CinetPay reçue', [
                'status_code' => $statusCode,
                'code' => $responseData['code'] ?? 'N/A',
                'message' => $responseData['message'] ?? 'N/A'
            ]);

            if ($statusCode === 200) {
                if (isset($responseData['code']) && $responseData['code'] === '201') {
                    Log::info('✅ Paiement initialisé avec succès', [
                        'transaction_id' => $paymentData['transaction_id'],
                        'payment_url' => $responseData['data']['payment_url'] ?? null,
                        'payment_token' => $responseData['data']['payment_token'] ?? null
                    ]);

                    return [
                        'success' => true,
                        'payment_url' => $responseData['data']['payment_url'],
                        'payment_token' => $responseData['data']['payment_token'],
                        'transaction_id' => $paymentData['transaction_id'],
                        'api_response' => $responseData
                    ];
                } else {
                    $errorMsg = $responseData['message'] ?? 'Erreur inconnue de CinetPay';
                    $errorCode = $responseData['code'] ?? 'unknown';
                    
                    Log::error('❌ Erreur CinetPay lors de l\'initialisation', [
                        'error_code' => $errorCode,
                        'error_message' => $errorMsg,
                        'full_response' => $responseData
                    ]);

                    return [
                        'success' => false,
                        'error' => $errorMsg,
                        'code' => $errorCode,
                        'full_response' => $responseData
                    ];
                }
            } else {
                Log::error('❌ Statut HTTP invalide de CinetPay', [
                    'status_code' => $statusCode,
                    'response_body' => $responseBody
                ]);

                return [
                    'success' => false,
                    'error' => 'Erreur HTTP: ' . $statusCode,
                    'body' => $responseBody
                ];
            }

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorDetails = [
                'message' => $e->getMessage(),
                'transaction_id' => $paymentData['transaction_id']
            ];

            if ($e->hasResponse()) {
                $errorDetails['response_body'] = $e->getResponse()->getBody()->getContents();
                $errorDetails['status_code'] = $e->getResponse()->getStatusCode();
            }

            Log::error('❌ Exception Request CinetPay', $errorDetails);

            return [
                'success' => false,
                'error' => 'Erreur de connexion à CinetPay: ' . $e->getMessage(),
                'details' => $errorDetails
            ];
        } catch (\Exception $e) {
            Log::error('❌ Exception générale CinetPay', [
                'error' => $e->getMessage(),
                'transaction_id' => $paymentData['transaction_id'],
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Erreur interne: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Vérifier le statut d'un paiement (avec SSL désactivé temporairement)
     */
    public function checkPaymentStatus($transactionId)
    {
        Log::info('🔍 Vérification statut paiement CinetPay', ['transaction_id' => $transactionId]);

        try {
            $payload = [
                "apikey" => $this->apiKey,
                "site_id" => $this->siteId,
                "transaction_id" => $transactionId
            ];

            // ✅ SSL désactivé temporairement
            $client = new \GuzzleHttp\Client([
                'verify' => false,
                'timeout' => 30,
                'http_errors' => false,
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false
                ]
            ]);

            $response = $client->post($this->baseUrl . '/payment/check', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload
            ]);

            $responseData = json_decode($response->getBody(), true);

            Log::info('📊 Résultat vérification statut', [
                'transaction_id' => $transactionId,
                'status_code' => $response->getStatusCode(),
                'code' => $responseData['code'] ?? 'N/A'
            ]);

            return [
                'success' => true,
                'data' => $responseData
            ];

        } catch (\Exception $e) {
            Log::error('❌ Erreur vérification statut', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Formater le numéro de téléphone pour CinetPay
     */
    private function formatPhoneNumber($phoneNumber)
    {
        Log::info('📞 Formatage numéro téléphone', ['original' => $phoneNumber]);
        
        // Nettoyer le numéro
        $cleaned = preg_replace('/[^0-9+]/', '', $phoneNumber);
        
        // S'assurer qu'il commence par +225 pour la Côte d'Ivoire
        if (strpos($cleaned, '+225') === 0) {
            $formatted = $cleaned;
        } elseif (strpos($cleaned, '225') === 0) {
            $formatted = '+' . $cleaned;
        } elseif (strpos($cleaned, '0') === 0 && strlen($cleaned) === 10) {
            $formatted = '+225' . substr($cleaned, 1);
        } else {
            $formatted = '+225' . $cleaned;
        }
        
        Log::info('📞 Numéro formaté', ['original' => $phoneNumber, 'formaté' => $formatted]);
        
        return $formatted;
    }

    /**
     * Vérifier la signature CinetPay
     */
    public function verifySignature($data, $signature)
    {
        Log::info('🔐 Vérification signature CinetPay', [
            'transaction_id' => $data['cpm_trans_id'] ?? 'inconnu'
        ]);

        try {
            $signatureData = ($data['cpm_trans_id'] ?? '') . $this->siteId . $this->apiKey;
            $computedSignature = hash('sha256', $signatureData);
            
            $isValid = hash_equals($computedSignature, $signature);
            
            Log::info('📝 Résultat vérification signature', [
                'transaction_id' => $data['cpm_trans_id'] ?? 'inconnu',
                'is_valid' => $isValid
            ]);
            
            return $isValid;

        } catch (\Exception $e) {
            Log::error('❌ Erreur vérification signature', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}