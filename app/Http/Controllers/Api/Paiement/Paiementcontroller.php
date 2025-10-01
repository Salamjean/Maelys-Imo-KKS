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
 
/**
     * @OA\Post(
     *     path="/api/tenant/{locataireId}/paiements",
     *     summary="Créer un nouveau paiement pour un locataire",
     *     description="Initie un paiement de loyer avec deux méthodes supportées : mobile_money (CinetPay) et virement bancaire",
     *     tags={"Paiements"},
     *     @OA\Parameter(
     *         name="locataireId",
     *         in="path",
     *         required=true,
     *         description="ID du locataire",
     *         @OA\Schema(type="integer", example=123)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"methode_paiement"},
     *                 @OA\Property(
     *                     property="methode_paiement",
     *                     type="string",
     *                     enum={"mobile_money", "virement"},
     *                     description="Méthode de paiement choisie",
     *                     example="mobile_money"
     *                 ),
     *                 @OA\Property(
     *                     property="proof_file",
     *                     type="string",
     *                     format="binary",
     *                     description="Fichier de preuve de virement (requis pour virement) - Formats: jpg, jpeg, png, pdf (max 2MB)"
     *                 ),
     *                 @OA\Property(
     *                     property="transaction_id",
     *                     type="string",
     *                     description="ID de transaction optionnel",
     *                     example="PAY_1700000000"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paiement mobile money initié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Paiement initié avec succès. Redirection vers CinetPay..."),
     *             @OA\Property(property="payment_url", type="string", example="https://checkout.cinetpay.com/payment/abc123"),
     *             @OA\Property(property="payment_token", type="string", example="cp_token_xyz789"),
     *             @OA\Property(property="transaction_id", type="string", example="PAY_1700000000"),
     *             @OA\Property(property="mode", type="string", example="PRODUCTION")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Paiement par virement enregistré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Paiement enregistré avec succès pour le mois de Janvier 2024"),
     *             @OA\Property(property="paiement", ref="#/components/schemas/Paiement")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation ou de traitement",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur interne",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur lors de l'enregistrement du paiement"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
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
            $transactionId = $request->transaction_id ?? 'PAY_' . time();

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


/**
     * @OA\Post(
     *     path="/api/paiement/cinetpay/notify",
     *     summary="Webhook de notification CinetPay",
     *     description="Endpoint de callback pour les notifications de paiement CinetPay",
     *     tags={"Paiements - Webhooks"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(property="cpm_trans_id", type="string", description="ID de transaction CinetPay"),
     *                 @OA\Property(property="cpm_site_id", type="string", description="ID du site CinetPay"),
     *                 @OA\Property(property="cpm_amount", type="string", description="Montant de la transaction"),
     *                 @OA\Property(property="cel_phone_num", type="string", description="Numéro de téléphone du payeur"),
     *                 @OA\Property(property="payment_method", type="string", description="Méthode de paiement"),
     *                 @OA\Property(property="cpm_error_message", type="string", description="Message d'erreur (SUCCES en cas de succès)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification traitée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Paiement traité")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Données invalides ou manquantes",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur interne",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
public function handleCinetPayNotification(Request $request)
{
    Log::info('=== NOTIFICATION CINETPAY REÇUE ===');
    Log::info('Données reçues:', $request->all());

    try {
        // CinetPay envoie les données en x-www-form-urlencoded
        $transactionId = $request->input('cpm_trans_id');
        $siteId = $request->input('cpm_site_id');
        $amount = $request->input('cpm_amount');
        $phoneNumber = $request->input('cel_phone_num');
        $paymentMethod = $request->input('payment_method');
        $errorMessage = $request->input('cpm_error_message', '');

        // Vérification des données obligatoires
        if (!$transactionId || !$siteId) {
            Log::error('Données obligatoires manquantes');
            return response()->json(['status' => 'error', 'message' => 'Données manquantes'], 400);
        }

        // Vérifier que le site_id correspond
        $configSiteId = config('services.cinetpay.site_id');
        if ($siteId !== $configSiteId) {
            Log::error('Site ID mismatch');
            return response()->json(['status' => 'error', 'message' => 'Site ID invalide'], 400);
        }

        // ✅ SUPPRIMÉ : Plus de vérification de signature

        // Déterminer le statut basé sur cpm_error_message
        $statutFinal = ($errorMessage === 'SUCCES') ? 'payé' : 'échoué';

        Log::info('Traitement notification', [
            'transaction' => $transactionId,
            'error_message' => $errorMessage,
            'statut_final' => $statutFinal
        ]);

        // Récupérer la session de paiement
        $paiementSession = PaiementSession::where('transaction_id', $transactionId)
            ->where('expires_at', '>', now())
            ->first();

        if (!$paiementSession) {
            Log::error('Session de paiement non trouvée');
            return response()->json(['status' => 'error', 'message' => 'Session de paiement invalide'], 400);
        }

        // Vérifier si le paiement existe déjà
        $existingPayment = Paiement::where('transaction_id', $transactionId)->first();
        
        if ($existingPayment) {
            // Mettre à jour le paiement existant
            $existingPayment->update([
                'statut' => $statutFinal,
                'date_paiement' => $statutFinal === 'payé' ? now() : null,
                'phone_number' => $phoneNumber,
                'metadata' => array_merge($existingPayment->metadata ?? [], [
                    'updated_at' => now(),
                    'payment_method' => $paymentMethod,
                    'error_message' => $errorMessage,
                    'notify_data' => $request->all()
                ])
            ]);

            Log::info('Paiement mis à jour', [
                'transaction' => $transactionId,
                'statut' => $statutFinal
            ]);

        } else {
            // Créer un nouveau paiement
            do {
                $reference = 'PAY-' . str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
            } while (Paiement::where('reference', $reference)->exists());

            Paiement::create([
                'montant' => $amount,
                'date_paiement' => $statutFinal === 'payé' ? now() : null,
                'mois_couvert' => $paiementSession->mois_couvert,
                'methode_paiement' => 'Mobile Money',
                'statut' => $statutFinal,
                'reference' => $reference,
                'locataire_id' => $paiementSession->locataire_id,
                'bien_id' => $paiementSession->bien_id,
                'transaction_id' => $transactionId,
                'phone_number' => $phoneNumber,
                'metadata' => [
                    'payment_method' => $paymentMethod,
                    'error_message' => $errorMessage,
                    'notify_data' => $request->all()
                ]
            ]);

            Log::info('Nouveau paiement créé', [
                'transaction' => $transactionId,
                'statut' => $statutFinal
            ]);
        }

        // Si paiement réussi, réinitialiser montant majoré
        if ($statutFinal === 'payé') {
            $locataire = Locataire::with('bien')->find($paiementSession->locataire_id);
            if ($locataire && $locataire->bien->montant_majore) {
                $locataire->bien->update(['montant_majore' => null]);
                Log::info('Montant majoré réinitialisé');
            }
        }

        // Marquer la session comme utilisée
        $paiementSession->update(['used_at' => now()]);

        Log::info('✅ Notification traitée avec succès');

        return response()->json(['status' => 'success', 'message' => 'Paiement traité']);

    } catch (\Exception $e) {
        Log::error('❌ Erreur traitement notification: ' . $e->getMessage());
        return response()->json(['status' => 'error', 'message' => 'Erreur serveur'], 500);
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

/**
     * @OA\Get(
     *     path="/api/paiement/check/{transactionId}",
     *     summary="Vérifier le statut d'un paiement",
     *     description="Retourne le statut actuel d'un paiement via son ID de transaction",
     *     tags={"Paiements"},
     *     @OA\Parameter(
     *         name="transactionId",
     *         in="path",
     *         required=true,
     *         description="ID de la transaction",
     *         @OA\Schema(type="string", example="PAY_1700000000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Statut récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="statut", type="string", example="payé"),
     *             @OA\Property(property="paiement", ref="#/components/schemas/Paiement")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Paiement non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Paiement non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur interne",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
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

        return response()->json([
            'success' => true,
            'statut' => $paiement->statut,
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


/**
     * @OA\Get(
     *     path="/api/tenant/{locataireId}/paiements",
     *     summary="Lister les paiements d'un locataire",
     *     description="Retourne tous les paiements d'un locataire spécifique avec les détails du bien",
     *     tags={"Paiements"},
     *     @OA\Parameter(
     *         name="locataireId",
     *         in="path",
     *         required=true,
     *         description="ID du locataire",
     *         @OA\Schema(type="integer", example=123)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paiements récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="locataire", ref="#/components/schemas/Locataire"),
     *                 @OA\Property(property="message", type="string", example="Paiements récupérés avec succès")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Locataire non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Locataire non trouvé")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/tenant/paiements/{id}",
     *     summary="Afficher les détails d'un paiement",
     *     description="Retourne les détails complets d'un paiement spécifique",
     *     tags={"Paiements"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du paiement",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du paiement récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Paiement"),
     *             @OA\Property(property="message", type="string", example="Détails du paiement récupérés avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Paiement non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Paiement non trouvé")
     *         )
     *     )
     * )
     */

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

    /**
     * @OA\Get(
     *     path="/api/tenant/paiement/mon-qr-code",
     *     summary="Obtenir le QR code de paiement du locataire",
     *     description="Retourne le QR code actif du locataire authentifié pour le paiement en espèces",
     *     tags={"Paiements - QR Code"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="QR code récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="code", type="string", example="ABC123"),
     *                 @OA\Property(property="expires_at", type="string", format="date-time"),
     *                 @OA\Property(property="expires_in", type="string", example="23 heures"),
     *                 @OA\Property(property="montant_total", type="number", format="float", example=150000),
     *                 @OA\Property(property="nombre_mois", type="integer", example=3),
     *                 @OA\Property(property="mois_couverts", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="qr_code_url", type="string", format="uri"),
     *                 @OA\Property(property="is_valid", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Accès réservé aux locataires")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Aucun code QR actif trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Aucun code QR actif trouvé")
     *         )
     *     )
     * )
     */

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

 /**
     * @OA\Get(
     *     path="/api/paiement/success",
     *     summary="Page de succès après paiement",
     *     description="Page de redirection après un paiement réussi via CinetPay",
     *     tags={"Paiements - Redirections"},
     *     @OA\Parameter(
     *         name="transaction_id",
     *         in="query",
     *         required=false,
     *         description="ID de transaction CinetPay",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Page HTML de succès"
     *     )
     * )
     */

public function paymentSuccess(Request $request)
{
    Log::info('=== RETOUR SUCCESS CINETPAY ===');
    Log::info('Données reçues:', $request->all());

    // CinetPay envoie "transaction_id" dans la redirection
    $transactionId = $request->input('transaction_id');

    if (!$transactionId) {
        // Page d'erreur stylisée
        return view('locataire.paiements.error', [
            'message' => 'Transaction ID manquant'
        ]);
    }
    
    // Récupérer le paiement
    $paiement = Paiement::where('transaction_id', $transactionId)->first();
    
    if ($paiement) {
        return view('locataire.paiements.success', [
            'paiement' => [
                'id' => $paiement->id,
                'reference' => $paiement->reference,
                'montant' => $paiement->montant,
                'date_paiement' => $paiement->date_paiement,
                'mois_couvert' => $paiement->mois_couvert,
                'methode_paiement' => $paiement->methode_paiement,
                'statut' => $paiement->statut
            ]
        ]);
    }
    
    return view('locataire.paiements.error', [
        'message' => 'Paiement non trouvé pour la transaction: ' . $transactionId
    ]);
}

/**
     * @OA\Get(
     *     path="/api/paiement/cancel",
     *     summary="Annulation de paiement",
     *     description="Endpoint appelé lorsque l'utilisateur annule un paiement CinetPay",
     *     tags={"Paiements - Redirections"},
     *     @OA\Parameter(
     *         name="cpm_trans_id",
     *         in="query",
     *         required=false,
     *         description="ID de transaction CinetPay",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paiement annulé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Paiement annulé par l'utilisateur"),
     *             @OA\Property(property="transaction_id", type="string")
     *         )
     *     )
     * )
     */
public function paymentCancel(Request $request)
{
    Log::info('=== RETOUR CANCEL CINETPAY ===');
    Log::info('Données reçues:', $request->all());

    $transactionId = $request->input('cpm_trans_id');
    
    return response()->json([
        'success' => false,
        'message' => 'Paiement annulé par l\'utilisateur',
        'transaction_id' => $transactionId
    ]);
}
}