<?php

namespace App\Http\Controllers\Api\Paiement;

use App\Http\Controllers\Controller;
use App\Models\CashVerificationCode;
use App\Models\Locataire;
use App\Models\Paiement;
use App\Models\PaiementSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Paiementcontroller extends Controller
{
 
public function store(Request $request, $locataireId)
{
    $request->validate([
        'methode_paiement' => 'required|in:mobile_money,virement',
        'proof_file' => 'required_if:methode_paiement,virement|file|mimes:jpg,jpeg,png,pdf|max:2048',
    ]);

    try {
        $locataire = Locataire::with('bien')->findOrFail($locataireId);

        // Vérifier que le locataire a un numéro de téléphone valide
        if (empty($locataire->contact)) {
            return response()->json([
                'success' => false,
                'message' => 'Le locataire doit avoir un numéro de téléphone valide pour effectuer un paiement.'
            ], 422);
        }

        // Déterminer automatiquement le mois à payer
        $dernierPaiement = Paiement::where('locataire_id', $locataire->id)
            ->where('statut', 'payé')
            ->orderBy('mois_couvert', 'desc')
            ->first();

        $moisAPayer = $dernierPaiement 
            ? Carbon::parse($dernierPaiement->mois_couvert)->addMonth()
            : now();

        // Vérifier si ce mois n'a pas déjà été payé
        $paiementExistant = Paiement::where('locataire_id', $locataire->id)
            ->where('mois_couvert', $moisAPayer->format('Y-m'))
            ->where('statut', 'payé')
            ->exists();

        if ($paiementExistant) {
            return response()->json([
                'success' => false,
                'message' => 'Le loyer pour '.$moisAPayer->translatedFormat('F Y').' a déjà été payé.'
            ], 422);
        }

        $montant = $locataire->bien->montant_majore ?? $locataire->bien->prix;

        // Vérifier que le montant est valide pour la production
        if ($montant < 100) { // Minimum 100 FCFA pour CinetPay
            return response()->json([
                'success' => false,
                'message' => 'Le montant minimum pour un paiement est de 100 FCFA.'
            ], 422);
        }

        // Si méthode de paiement est mobile_money
        if ($request->methode_paiement === 'mobile_money') {
            $transactionId = $request->transaction_id ?? 'PAY_' . time() . '_' . mt_rand(100000, 999999);

            // Stocker les données dans la table de session
            PaiementSession::create([
                'transaction_id' => $transactionId,
                'locataire_id' => $locataire->id,
                'bien_id' => $locataire->bien_id,
                'montant' => $montant,
                'mois_couvert' => $moisAPayer->format('Y-m'),
                'metadata' => [
                    'customer_name' => $locataire->name,
                    'customer_surname' => $locataire->prenom,
                    'customer_phone' => $locataire->contact,
                    'description' => 'Paiement loyer ' . $moisAPayer->translatedFormat('F Y'),
                ],
                'expires_at' => now()->addHours(24),
            ]);

            // Préparer les données pour CinetPay en PRODUCTION
            $baseUrl = config('app.url');
            
            $cinetPayData = [
                'apikey' => config('services.cinetpay.api_key'),
                'site_id' => config('services.cinetpay.site_id'),
                'transaction_id' => $transactionId,
                'amount' => $montant,
                'currency' => 'XOF',
                'description' => 'Paiement loyer ' . $moisAPayer->translatedFormat('F Y'),
                'customer_id' => (string) $locataire->id,
                'customer_name' => $locataire->name,
                'customer_surname' => $locataire->prenom,
                'customer_email' => $locataire->email ?? $locataire->contact . '@locataire.com',
                'customer_phone_number' => $this->formatPhoneNumber($locataire->contact),
                'customer_address' => $locataire->adresse ?? 'Adresse non spécifiée',
                'customer_city' => $locataire->bien->commune ?? 'Abidjan',
                'customer_country' => 'CI',
                'customer_state' => 'CI',
                'customer_zip_code' => '00225',
                'notify_url' => $baseUrl . '/api/paiement/cinetpay/notify',
                'return_url' => $baseUrl . '/api/paiement/success',
                'channels' => 'ALL',
                'metadata' => (string) $locataire->id,
                'lang' => 'FR'
            ];

            // Faire l'appel à CinetPay en PRODUCTION
            $client = new \GuzzleHttp\Client();
            
            $response = $client->post('https://api-checkout.cinetpay.com/v2/payment', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $cinetPayData,
                'timeout' => 30,
                'verify' => true, // SSL vérifié en production
            ]);

            $responseData = json_decode($response->getBody(), true);

            Log::info('Réponse CinetPay PRODUCTION:', $responseData);

            if ($responseData['code'] == '201') {
                return response()->json([
                    'success' => true,
                    'message' => 'Paiement initié avec succès. Redirection vers CinetPay...',
                    'payment_url' => $responseData['data']['payment_url'],
                    'payment_token' => $responseData['data']['payment_token'],
                    'transaction_id' => $transactionId,
                    'mode' => 'PRODUCTION'
                ], 200);
            } else {
                throw new \Exception('Erreur CinetPay: ' . ($responseData['message'] ?? 'Unknown error'));
            }
        }

            // Si méthode de paiement est virement
            if ($request->methode_paiement === 'virement') {
                $transaction_id = $request->transaction_id ?? 'VIR_' . Str::random(10);

                // Gestion du fichier de preuve
                $proofPath = null;
                if ($request->hasFile('proof_file')) {
                    $proofPath = $request->file('proof_file')->store('preuves_virements', 'public');
                }

                // Générer une référence unique
                do {
                    $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
                    $numeroId = 'PAY-' . $randomNumber;
                } while (Paiement::where('reference', $numeroId)->exists());

                // Enregistrer le paiement
                $paiement = Paiement::create([
                    'montant' => $montant,
                    'date_paiement' => now(),
                    'mois_couvert' => $moisAPayer->format('Y-m'),
                    'methode_paiement' => 'Virement Bancaire',
                    'statut' => 'En attente',
                    'reference' => $numeroId,
                    'locataire_id' => $locataire->id,
                    'bien_id' => $locataire->bien_id,
                    'transaction_id' => $transaction_id,
                    'proof_path' => $proofPath,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Paiement enregistré avec succès pour le mois de '.$moisAPayer->translatedFormat('F Y'),
                    'paiement' => $paiement
                ], 201);
            }

        } catch (\Exception $e) {
            Log::error('Erreur store paiement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement du paiement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Ajoutez cette méthode pour formater le numéro de téléphone
private function formatPhoneNumber($phone)
{
    // Nettoyer le numéro
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Si le numéro commence par 0, le convertir en format international
    if (substr($phone, 0, 1) === '0') {
        $phone = '+225' . substr($phone, 1);
    }
    // Si le numéro commence par 225 sans le +, ajouter le +
    elseif (substr($phone, 0, 3) === '225') {
        $phone = '+' . $phone;
    }
    // Si le numéro n'a pas d'indicatif, ajouter +225
    elseif (strlen($phone) === 10) {
        $phone = '+225' . $phone;
    }
    
    return $phone;
}

public function handleCinetPayNotification(Request $request)
{
    Log::info('=== NOTIFICATION CINETPAY PRODUCTION (FORM URLENCODED) ===');
    Log::info('Données reçues:', $request->all());

    try {
        // CinetPay envoie les données en x-www-form-urlencoded, pas en JSON
        $transactionId = $request->input('cpm_trans_id');
        $siteId = $request->input('cpm_site_id');
        $amount = $request->input('cpm_amount');
        $currency = $request->input('cpm_currency');
        $status = $request->input('cpm_result');
        $transDate = $request->input('cpm_trans_date');
        $phoneNumber = $request->input('cel_phone_num');
        $paymentMethod = $request->input('payment_method');
        $errorMessage = $request->input('cpm_error_message', '');
        $signature = $request->input('signature');

        // Vérification des données obligatoires
        if (!$transactionId || !$siteId) {
            Log::error('Données obligatoires manquantes', [
                'cpm_trans_id' => $transactionId,
                'cpm_site_id' => $siteId
            ]);
            return response()->json(['status' => 'error', 'message' => 'Données manquantes'], 400);
        }

        // Vérifier que le site_id correspond
        $configSiteId = config('services.cinetpay.site_id');
        if ($siteId !== $configSiteId) {
            Log::error('Site ID mismatch', [
                'received' => $siteId,
                'expected' => $configSiteId
            ]);
            return response()->json(['status' => 'error', 'message' => 'Site ID invalide'], 400);
        }

        // Vérifier la signature en production
        if (config('services.cinetpay.mode') === 'PRODUCTION') {
            if (!$this->verifyCinetPaySignature($request)) {
                Log::error('Signature CinetPay invalide', [
                    'transaction_id' => $transactionId,
                    'received_signature' => $signature
                ]);
                return response()->json(['status' => 'error', 'message' => 'Signature invalide'], 400);
            }
        }

        // Étape 2: Vérifier l'état de la transaction via l'API CinetPay
        $verificationResult = $this->verifyTransactionWithCinetPay($transactionId);
        
        if (!$verificationResult['success']) {
            Log::error('Échec vérification transaction CinetPay', [
                'transaction_id' => $transactionId,
                'error' => $verificationResult['error']
            ]);
            return response()->json(['status' => 'error', 'message' => 'Échec vérification transaction'], 400);
        }

        $transactionData = $verificationResult['data'];
        
        // Utiliser le statut de l'API de vérification, pas celui de la notification
        $realStatus = $transactionData['data']['status'] ?? $status;
        $realAmount = $transactionData['data']['amount'] ?? $amount;

        Log::info('Statut réel de la transaction', [
            'transaction_id' => $transactionId,
            'status_notification' => $status,
            'status_verification' => $realStatus,
            'data' => $transactionData
        ]);

        // Récupérer la session de paiement
        $paiementSession = PaiementSession::where('transaction_id', $transactionId)
            ->where('expires_at', '>', now())
            ->first();

        if (!$paiementSession) {
            Log::error('Session de paiement non trouvée', ['transaction_id' => $transactionId]);
            return response()->json(['status' => 'error', 'message' => 'Session de paiement invalide'], 400);
        }

        // Vérifier si le paiement existe déjà
        $existingPayment = Paiement::where('transaction_id', $transactionId)->first();
        
        if ($existingPayment) {
            // Ne mettre à jour que si le statut a changé
            $nouveauStatut = $this->mapCinetPayStatus($realStatus);
            if ($existingPayment->statut !== $nouveauStatut) {
                $existingPayment->update([
                    'statut' => $nouveauStatut,
                    'date_paiement' => $realStatus === 'ACCEPTED' ? now() : $existingPayment->date_paiement,
                    'phone_number' => $phoneNumber ?? $existingPayment->phone_number,
                    'metadata' => array_merge($existingPayment->metadata ?? [], [
                        'payment_method' => $paymentMethod ?? 'MOBILE_MONEY',
                        'operator' => $request->input('cel_operator'),
                        'error_message' => $errorMessage,
                        'trans_date' => $transDate ?? now(),
                        'notify_data' => $request->all(),
                        'verification_data' => $transactionData,
                        'status_updated_at' => now(),
                        'mode' => 'PRODUCTION'
                    ])
                ]);

                Log::info('Statut paiement mis à jour via notification', [
                    'transaction_id' => $transactionId,
                    'ancien_statut' => $existingPayment->getOriginal('statut'),
                    'nouveau_statut' => $nouveauStatut,
                    'statut_cinetpay' => $realStatus
                ]);
            }

            return response()->json(['status' => 'success', 'message' => 'Statut traité']);
        }

        // Vérifier si le mois n'a pas déjà été payé (uniquement pour les succès)
        if ($realStatus === 'ACCEPTED') {
            $paiementExistant = Paiement::where('locataire_id', $paiementSession->locataire_id)
                ->where('mois_couvert', $paiementSession->mois_couvert)
                ->where('statut', 'payé')
                ->exists();

            if ($paiementExistant) {
                Log::warning('Mois déjà payé - transaction rejetée', [
                    'locataire_id' => $paiementSession->locataire_id,
                    'mois_couvert' => $paiementSession->mois_couvert
                ]);
                return response()->json(['status' => 'error', 'message' => 'Mois déjà payé'], 409);
            }
        }

        // Générer une référence unique
        do {
            $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $numeroId = 'PAY-' . $randomNumber;
        } while (Paiement::where('reference', $numeroId)->exists());

        // Enregistrer le paiement
        $paiement = Paiement::create([
            'montant' => $realAmount,
            'date_paiement' => $realStatus === 'ACCEPTED' ? now() : null,
            'mois_couvert' => $paiementSession->mois_couvert,
            'methode_paiement' => 'Mobile Money',
            'statut' => $this->mapCinetPayStatus($realStatus),
            'reference' => $numeroId,
            'locataire_id' => $paiementSession->locataire_id,
            'bien_id' => $paiementSession->bien_id,
            'transaction_id' => $transactionId,
            'proof_path' => null,
            'phone_number' => $phoneNumber,
            'metadata' => [
                'payment_method' => $paymentMethod ?? 'MOBILE_MONEY',
                'operator' => $request->input('cel_operator'),
                'error_message' => $errorMessage,
                'error_code' => $realStatus,
                'trans_date' => $transDate ?? now(),
                'notify_data' => $request->all(),
                'verification_data' => $transactionData,
                'mode' => 'PRODUCTION',
                'status_message' => $this->getStatusMessage($realStatus, $errorMessage),
                'failure_reason' => $this->getFailureReason($realStatus, $errorMessage)
            ]
        ]);

        // Marquer la session comme utilisée
        $paiementSession->update(['used_at' => now()]);

        // Si le paiement est réussi
        if ($realStatus === 'ACCEPTED') {
            $locataire = Locataire::with('bien')->find($paiementSession->locataire_id);
            if ($locataire && $locataire->bien->montant_majore) {
                $locataire->bien->update(['montant_majore' => null]);
            }

            Log::info('✅ Paiement PRODUCTION réussi traité', [
                'paiement_id' => $paiement->id,
                'transaction_id' => $transactionId,
                'montant' => $realAmount
            ]);
        } else {
            Log::warning('❌ Paiement PRODUCTION échoué', [
                'paiement_id' => $paiement->id,
                'transaction_id' => $transactionId,
                'statut' => $realStatus,
                'error_message' => $errorMessage
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Paiement traité']);

    } catch (\Exception $e) {
        Log::error('💥 Erreur notification CinetPay: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        return response()->json(['status' => 'error', 'message' => 'Erreur interne du serveur'], 500);
    }
}

/**
 * Vérifier la transaction via l'API CinetPay
 */
private function verifyTransactionWithCinetPay($transactionId)
{
    try {
        $apiKey = config('services.cinetpay.api_key');
        $siteId = config('services.cinetpay.site_id');
        
        $data = [
            'apikey' => $apiKey,
            'site_id' => $siteId,
            'transaction_id' => $transactionId
        ];

        $client = new \GuzzleHttp\Client();
        $response = $client->post('https://api-checkout.cinetpay.com/v2/payment/check', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
            'timeout' => 10,
        ]);

        $responseData = json_decode($response->getBody(), true);

        Log::info('Réponse vérification CinetPay', [
            'transaction_id' => $transactionId,
            'response' => $responseData
        ]);

        return [
            'success' => true,
            'data' => $responseData
        ];

    } catch (\Exception $e) {
        Log::error('Erreur vérification CinetPay', [
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
 * Vérifier la signature CinetPay pour x-www-form-urlencoded
 */
private function verifyCinetPaySignature(Request $request)
{
    $transactionId = $request->input('cpm_trans_id');
    $siteId = $request->input('cpm_site_id');
    $apiKey = config('services.cinetpay.api_key');
    $receivedSignature = $request->input('signature');
    
    // Ancienne méthode MD5 (ne marche plus)
    // $signatureData = $transactionId . $siteId . $apiKey;
    // $computedSignature = md5($signatureData);
    
    // Nouvelle méthode HMAC SHA256
    $signatureData = $transactionId . $siteId . $apiKey;
    $computedSignature = hash_hmac('sha256', $signatureData, $apiKey);
    
    $isValid = hash_equals($computedSignature, $receivedSignature);
    
    if (!$isValid) {
        Log::warning('Signature invalide', [
            'computed' => $computedSignature,
            'received' => $receivedSignature,
            'data' => $signatureData
        ]);
        
        // Pour debug, loguez aussi l'ancienne méthode MD5
        $md5Signature = md5($signatureData);
        Log::info('Signature MD5 (ancienne): ' . $md5Signature);
    }
    
    return $isValid;
}

/**
 * Mapper les statuts CinetPay (nouvelle version)
 */
private function mapCinetPayStatus($cinetPayStatus)
{
    $statusMap = [
        'ACCEPTED' => 'payé',
        'REFUSED' => 'échoué',
        'PENDING' => 'en_attente',
        'CANCELED' => 'annulé',
        'EXPIRED' => 'expiré',
        'WAITING_FOR_CUSTOMER' => 'en_attente',
        '00' => 'payé',
        '01' => 'échoué',
        '02' => 'en_attente',
        '03' => 'annulé',
        '04' => 'en_attente',
        '05' => 'expiré',
    ];

    return $statusMap[$cinetPayStatus] ?? 'en_attente';
}

  /**
 * Obtenir la raison de l'échec en français
 */
private function getFailureReason($status, $errorMessage)
{
    $reasons = [
        '01' => 'Paiement refusé',
        '02' => 'Paiement en attente de confirmation',
        '03' => 'Paiement annulé par l\'utilisateur',
        '04' => 'Paiement en cours de traitement',
        '05' => 'Transaction expirée',
        '06' => 'Solde insuffisant',
        '07' => 'Numéro de téléphone invalide',
        '08' => 'Opérateur non disponible',
        '09' => 'Erreur technique',
        '10' => 'Compte marchand suspendu',
        '11' => 'Montant invalide',
        '12' => 'Devise non supportée',
        '13' => 'Doublon de transaction',
        '14' => 'Timeout de traitement',
        '15' => 'Signature invalide'
    ];

    $defaultReason = $errorMessage ?: 'Raison non spécifiée';
    
    return $reasons[$status] ?? $defaultReason;
}

/**
 * Obtenir un message de statut détaillé
 */
private function getStatusMessage($status, $errorMessage)
{
    $messages = [
        '00' => 'Paiement effectué avec succès',
        '01' => 'Paiement refusé : ' . $errorMessage,
        '02' => 'Paiement en attente de confirmation',
        '03' => 'Paiement annulé par l\'utilisateur',
        '04' => 'Paiement en cours de traitement',
        '05' => 'Transaction expirée',
    ];

    return $messages[$status] ?? 'Statut inconnu : ' . $status;
}


public function checkPaymentStatus($transactionId)
{
    try {
        $paiement = Paiement::where('transaction_id', $transactionId)->first();
        
        if (!$paiement) {
            return response()->json([
                'success' => false,
                'message' => 'Paiement non trouvé'
            ], 404);
        }

        // Si le statut est déjà connu, retourner directement
        if (in_array($paiement->statut, ['payé', 'échoué', 'annulé', 'expiré'])) {
            return response()->json([
                'success' => true,
                'statut' => $paiement->statut,
                'message' => $paiement->metadata['status_message'] ?? 'Statut déterminé',
                'paiement' => $paiement
            ]);
        }

        // Sinon, interroger CinetPay
        $apiKey = config('services.cinetpay.api_key');
        $siteId = config('services.cinetpay.site_id');
        
        $data = [
            'apikey' => $apiKey,
            'site_id' => $siteId,
            'transaction_id' => $transactionId
        ];

        $client = new \GuzzleHttp\Client();
        $response = $client->post('https://api-checkout.cinetpay.com/v2/payment/check', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => $data
        ]);

        $responseData = json_decode($response->getBody(), true);

        // Mettre à jour le statut si nécessaire
        if (isset($responseData['data']['status'])) {
            $nouveauStatut = $this->mapCinetPayStatus($responseData['data']['status']);
            $paiement->update(['statut' => $nouveauStatut]);
        }

        return response()->json([
            'success' => true,
            'statut' => $paiement->statut,
            'data' => $responseData,
            'paiement' => $paiement
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la vérification du paiement',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function index($locataireId)
    {
        try {
            // Récupérer le locataire avec ses paiements et son bien
            $locataire = Locataire::with(['paiements', 'bien'])->findOrFail($locataireId);
            
            // Formater les dates en français (si nécessaire)
            $locataire->paiements->transform(function ($paiement) {
                $paiement->created_at_formatted = Carbon::parse($paiement->created_at)->translatedFormat('d F Y');
                return $paiement;
            });

            return response()->json([
                'data' => [
                    'locataire' => $locataire,
                    'message' => 'Paiements récupérés avec succès'
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Locataire non trouvé'
            ], 404);
        }
    }

    public function show($id)
    {
        try {
            $paiement = Paiement::with([
                'locataire:id,name,prenom,email,contact',
                'bien:id,commune,type',
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $paiement,
                'message' => 'Détails du paiement récupérés avec succès'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Paiement non trouvé'
            ], 404);
        }
    }

    public function getMyQrCode(Request $request)
    {
        // Récupérer le locataire authentifié
        $locataire = Auth::guard('sanctum')->user();
        
        // Vérifier que l'utilisateur est bien un locataire
        if (!$locataire instanceof Locataire) {
            return response()->json([
                'success' => false,
                'message' => 'Accès réservé aux locataires'
            ], 403);
        }

        // Récupérer le dernier code de vérification non utilisé et non expiré
        $qrCode = CashVerificationCode::where('locataire_id', $locataire->id)
                    ->whereNull('used_at')
                    ->where('expires_at', '>', now())
                    ->where('is_archived', false)
                    ->latest()
                    ->first();

        if (!$qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun code QR actif trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'code' => $qrCode->code,
                'expires_at' => $qrCode->expires_at->toIso8601String(),
                'expires_in' => now()->diffInHours($qrCode->expires_at) . ' heures',
                'montant_total' => $qrCode->montant_total,
                'nombre_mois' => $qrCode->nombre_mois,
                'mois_couverts' => $qrCode->mois_couverts,
                'qr_code_url' => $qrCode->qr_code_path ? Storage::url($qrCode->qr_code_path) : null,
                'is_valid' => $qrCode->expires_at > now() && is_null($qrCode->used_at)
            ]
        ]);
    }



public function paymentSuccess(Request $request)
{
    $transactionId = $request->input('cpm_trans_id');
    
    // Récupérer le paiement
    $paiement = Paiement::where('transaction_id', $transactionId)->first();
    
    if ($paiement) {
        return response()->json([
            'success' => true,
            'message' => 'Paiement effectué avec succès',
            'paiement' => $paiement
        ]);
    }
    
    return response()->json([
        'success' => false,
        'message' => 'Paiement non trouvé'
    ], 404);
}

public function paymentCancel(Request $request)
{
    $transactionId = $request->input('cpm_trans_id');
    
    return response()->json([
        'success' => false,
        'message' => 'Paiement annulé par l\'utilisateur',
        'transaction_id' => $transactionId
    ]);
}
}