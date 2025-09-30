<?php

namespace App\Http\Controllers\Api\Paiement;

use App\Http\Controllers\Controller;
use App\Models\CashVerificationCode;
use App\Models\Locataire;
use App\Models\Paiement;
use App\Models\PaiementSession;
use App\Services\CinetPayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Paiements",
 *     description="Endpoints pour la gestion des paiements de loyer"
 * )
 */
class Paiementcontroller extends Controller
{
 /**
 * @OA\Post(
 *     path="/api/tenant/{locataireId}/paiements",
 *     summary="Enregistrer un nouveau paiement ou initialiser paiement Mobile Money",
 *     description="Cette endpoint permet d'enregistrer un paiement pour un locataire. 
 *                  Pour les paiements par virement bancaire, le paiement est enregistré immédiatement.
 *                  Pour les paiements Mobile Money, l'endpoint initialise la transaction et retourne les données nécessaires pour l'intégration CinetPay.",
 *     tags={"Paiements"},
 *     security={{"bearerAuth": {}}},
 *     
 *     @OA\Parameter(
 *         name="locataireId",
 *         in="path",
 *         description="ID du locataire pour lequel effectuer le paiement",
 *         required=true,
 *         @OA\Schema(type="integer", example=123)
 *     ),
 *     
 *     @OA\RequestBody(
 *         required=true,
 *         description="Données du paiement à enregistrer",
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"methode_paiement"},
 *                 @OA\Property(
 *                     property="methode_paiement",
 *                     type="string",
 *                     enum={"mobile_money", "virement"},
 *                     example="mobile_money",
 *                     description="Méthode de paiement choisie"
 *                 ),
 *                 @OA\Property(
 *                     property="transaction_id",
 *                     type="string",
 *                     example="MM_123456789",
 *                     description="ID de transaction (optionnel pour mobile money, généré automatiquement si non fourni)"
 *                 ),
 *                 @OA\Property(
 *                     property="proof_file",
 *                     type="string",
 *                     format="binary",
 *                     description="Fichier de preuve de virement (requis pour virement bancaire, formats: jpg, jpeg, png, pdf, max: 2MB)"
 *                 )
 *             )
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=200,
 *         description="Initialisation Mobile Money réussie",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="type", type="string", example="mobile_money_init"),
 *             @OA\Property(
 *                 property="cinetpay_data",
 *                 type="object",
 *                 description="Données nécessaires pour l'intégration CinetPay",
 *                 @OA\Property(property="api_key", type="string", example="YOUR_API_KEY"),
 *                 @OA\Property(property="site_id", type="string", example="YOUR_SITE_ID"),
 *                 @OA\Property(property="notify_url", type="string", example="NOTIFY_URL"),
 *                 @OA\Property(property="mode", type="string", example="PRODUCTION"),
 *                 @OA\Property(property="transaction_id", type="string", example="PAY_1703500000000"),
 *                 @OA\Property(property="amount", type="number", format="float", example=75000),
 *                 @OA\Property(property="currency", type="string", example="XOF"),
 *                 @OA\Property(property="description", type="string", example="Paiement loyer Décembre 2023"),
 *                 @OA\Property(property="customer_name", type="string", example="DUPONT"),
 *                 @OA\Property(property="customer_surname", type="string", example="Jean"),
 *                 @OA\Property(property="customer_phone_number", type="string", example="+2250700000000"),
 *                 @OA\Property(property="channels", type="string", example="ALL"),
 *                 @OA\Property(
 *                     property="metadata",
 *                     type="object",
 *                     example={
 *                         "locataire_id": 123,
 *                         "bien_id": 456,
 *                         "mois_couvert": "2023-12",
 *                         "montant": 75000
 *                     }
 *                 )
 *             )
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=201,
 *         description="Paiement par virement enregistré avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Paiement enregistré avec succès pour le mois de Décembre 2023"),
 *             @OA\Property(
 *                 property="paiement",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="montant", type="number", format="float", example=75000),
 *                 @OA\Property(property="date_paiement", type="string", format="date-time", example="2023-12-15 10:30:00"),
 *                 @OA\Property(property="mois_couvert", type="string", format="date", example="2023-12"),
 *                 @OA\Property(property="methode_paiement", type="string", example="Virement Bancaire"),
 *                 @OA\Property(property="statut", type="string", example="En attente"),
 *                 @OA\Property(property="reference", type="string", example="PAY-00123"),
 *                 @OA\Property(property="transaction_id", type="string", example="VIR_ABC123DEF"),
 *                 @OA\Property(property="proof_path", type="string", example="preuves_virements/fichier.jpg"),
 *                 @OA\Property(property="locataire_id", type="integer", example=123),
 *                 @OA\Property(property="bien_id", type="integer", example=456),
 *                 @OA\Property(property="created_at", type="string", format="date-time"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time")
 *             )
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=404,
 *         description="Locataire non trouvé",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Locataire non trouvé")
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=409,
 *         description="Paiement déjà existant",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Paiement déjà enregistré pour Décembre 2023")
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=422,
 *         description="Erreur de validation ou paiement déjà effectué",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Le loyer pour Décembre 2023 a déjà été payé."),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 example={
 *                     "methode_paiement": {"La méthode de paiement sélectionnée est invalide."},
 *                     "proof_file": {"Le fichier de preuve est requis pour les virements bancaires."}
 *                 }
 *             )
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur interne",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Erreur lors de l'enregistrement du paiement"),
 *             @OA\Property(property="error", type="string", example="Message d'erreur détaillé")
 *         )
 *     )
 * )
 */
public function store(Request $request, $locataireId)
{
    Log::info('Début de la méthode store - Enregistrement paiement', [
        'locataire_id' => $locataireId,
        'methode_paiement' => $request->methode_paiement,
        'transaction_id' => $request->transaction_id,
        'has_proof_file' => $request->hasFile('proof_file')
    ]);

    $request->validate([
        'methode_paiement' => 'required|in:mobile_money,virement',
        'proof_file' => 'required_if:methode_paiement,virement|file|mimes:jpg,jpeg,png,pdf|max:2048',
    ]);

    try {
        $locataire = Locataire::with('bien')->findOrFail($locataireId);

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

        // Si méthode de paiement est mobile_money
        if ($request->methode_paiement === 'mobile_money') {
            $transactionId = $request->transaction_id ?? 'PAY_' . time();
            
            // Stocker les données dans la table de session
            $paiementSession = PaiementSession::create([
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

            // Initialiser le paiement avec CinetPay
            $cinetPayService = new CinetPayService();
            
            $paymentData = [
                'transaction_id' => $transactionId,
                'amount' => $montant,
                'description' => 'Paiement loyer ' . $moisAPayer->translatedFormat('F Y'),
                'customer_id' => (string) $locataire->id,
                'customer_name' => $locataire->name,
                'customer_surname' => $locataire->prenom,
                'customer_email' => $locataire->email ?? 'client@example.com',
                'customer_phone_number' => $locataire->contact,
                'customer_address' => $locataire->adresse ?? '',
                'customer_city' => $locataire->ville ?? '',
                'customer_country' => 'CI',
                'metadata' => json_encode([
                    'locataire_id' => $locataire->id,
                    'bien_id' => $locataire->bien_id,
                    'mois_couvert' => $moisAPayer->format('Y-m'),
                    'montant' => $montant
                ])
            ];

            $paymentInit = $cinetPayService->initializePayment($paymentData);

            if (!$paymentInit['success']) {
                Log::error('Échec initialisation paiement CinetPay', [
                    'error' => $paymentInit['error'],
                    'transaction_id' => $transactionId
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'initialisation du paiement: ' . $paymentInit['error']
                ], 500);
            }

            $responseData = [
                'success' => true,
                'type' => 'mobile_money_init',
                'payment_data' => [
                    'payment_url' => $paymentInit['payment_url'],
                    'payment_token' => $paymentInit['payment_token'],
                    'transaction_id' => $transactionId,
                    'amount' => $montant,
                    'currency' => 'XOF',
                    'description' => 'Paiement loyer ' . $moisAPayer->translatedFormat('F Y'),
                    'customer_name' => $locataire->name,
                    'customer_surname' => $locataire->prenom,
                    'customer_phone_number' => $locataire->contact,
                ],
                'cinetpay_config' => [
                    'api_key' => config('services.cinetpay.api_key'),
                    'site_id' => config('services.cinetpay.site_id'),
                    'notify_url' => route('api.cinetpay.notify'),
                    'return_url' => route('api.cinetpay.return'),
                    'mode' => config('services.cinetpay.mode', 'PRODUCTION'),
                ]
            ];

            return response()->json([
                'success' => true,
                'type' => 'mobile_money_init',
                'payment_data' => [
                    'payment_url' => $paymentInit['payment_url'],
                    'payment_token' => $paymentInit['payment_token'],
                    'transaction_id' => $transactionId,
                    'amount' => $montant,
                    'currency' => 'XOF',
                    'description' => 'Paiement loyer ' . $moisAPayer->translatedFormat('F Y'),
                    'customer_name' => $locataire->name,
                    'customer_surname' => $locataire->prenom,
                    'customer_phone_number' => $locataire->contact,
                ],
                'cinetpay_config' => [
                    'api_key' => config('services.cinetpay.api_key'),
                    'site_id' => config('services.cinetpay.site_id'),
                    'notify_url' => route('api.cinetpay.notify'),
                    'return_url' => route('api.cinetpay.return'),
                    'mode' => config('services.cinetpay.mode', 'PRODUCTION'),
                ]
            ], 200);
        }

        // Si méthode de paiement est virement, traiter normalement (enregistrement immédiat)
        Log::info('Traitement paiement par virement');
        $transaction_id = $request->transaction_id ?? 'VIR_' . Str::random(10);
        Log::info('Transaction ID virement', ['transaction_id' => $transaction_id]);

        // Vérifier si le paiement existe déjà
        Log::info('Vérification si paiement existe déjà avec cette transaction');
        $existingPayment = Paiement::where('transaction_id', $transaction_id)->first();
        if ($existingPayment) {
            Log::warning('Paiement déjà existant avec cette transaction ID', [
                'transaction_id' => $transaction_id,
                'paiement_id' => $existingPayment->id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Paiement déjà enregistré pour ' . Carbon::parse($request->mois_couvert)->translatedFormat('F Y')
            ], 409);
        }

        // Gestion du fichier de preuve
        $proofPath = null;
        if ($request->hasFile('proof_file')) {
            Log::info('Traitement du fichier de preuve');
            $proofPath = $request->file('proof_file')->store('preuves_virements', 'public');
            Log::info('Fichier de preuve sauvegardé', ['proof_path' => $proofPath]);
        }

        // Déterminer la méthode et le statut
        $methode = 'Virement Bancaire';
        $statut = 'En attente';

        // Générer une référence unique
        Log::info('Génération de la référence unique');
        do {
            $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $numeroId = 'PAY-' . $randomNumber;
        } while (Paiement::where('reference', $numeroId)->exists());

        Log::info('Référence unique générée', ['reference' => $numeroId]);

        // Enregistrer le paiement (uniquement pour virement)
        Log::info('Création du paiement en base de données');
        $paiement = Paiement::create([
            'montant' => $montant,
            'date_paiement' => now(),
            'mois_couvert' => $moisAPayer->format('Y-m'),
            'methode_paiement' => $methode,
            'statut' => $statut,
            'reference' => $numeroId,
            'locataire_id' => $locataire->id,
            'bien_id' => $locataire->bien_id,
            'transaction_id' => $transaction_id,
            'proof_path' => $proofPath,
        ]);

        Log::info('Paiement créé avec succès', [
            'paiement_id' => $paiement->id,
            'reference' => $paiement->reference
        ]);

        // Réinitialiser le montant majoré si nécessaire
        if ($locataire->bien->montant_majore) {
            Log::info('Réinitialisation du montant majoré', [
                'ancien_montant_majore' => $locataire->bien->montant_majore
            ]);
            $locataire->bien->update(['montant_majore' => null]);
            Log::info('Montant majoré réinitialisé');
        }

        Log::info('Paiement par virement terminé avec succès');

        return response()->json([
            'success' => true,
            'message' => 'Paiement enregistré avec succès pour le mois de '.$moisAPayer->translatedFormat('F Y'),
            'paiement' => $paiement
        ], 201);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        Log::error('Locataire non trouvé', [
            'locataire_id' => $locataireId,
            'error' => $e->getMessage()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Locataire non trouvé'
        ], 404);
    } catch (\Exception $e) {
        Log::error('Erreur lors de l\'enregistrement du paiement', [
            'locataire_id' => $locataireId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de l\'enregistrement du paiement',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * @OA\Get(
 *     path="/api/tenant/{locataireId}/paiements",
 *     summary="Lister les paiements d'un locataire",
 *     description="Récupère la liste de tous les paiements d'un locataire spécifique avec les détails du bien associé",
 *     tags={"Paiements"},
 *     security={{"bearerAuth": {}}},
 *     
 *     @OA\Parameter(
 *         name="locataireId",
 *         in="path",
 *         description="ID du locataire",
 *         required=true,
 *         @OA\Schema(type="integer", example=123)
 *     ),
 *     
 *     @OA\Response(
 *         response=200,
 *         description="Liste des paiements récupérée avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="locataire",
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=123),
 *                     @OA\Property(property="name", type="string", example="DUPONT"),
 *                     @OA\Property(property="prenom", type="string", example="Jean"),
 *                     @OA\Property(property="email", type="string", example="jean.dupont@example.com"),
 *                     @OA\Property(property="contact", type="string", example="+2250700000000"),
 *                     @OA\Property(
 *                         property="bien",
 *                         type="object",
 *                         @OA\Property(property="id", type="integer", example=456),
 *                         @OA\Property(property="prix", type="number", format="float", example=75000),
 *                         @OA\Property(property="commune", type="string", example="Cocody"),
 *                         @OA\Property(property="type", type="string", example="Appartement")
 *                     ),
 *                     @OA\Property(
 *                         property="paiements",
 *                         type="array",
 *                         @OA\Items(ref="#/components/schemas/Paiement")
 *                     )
 *                 ),
 *                 @OA\Property(property="message", type="string", example="Paiements récupérés avec succès")
 *             )
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=404,
 *         description="Locataire non trouvé",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Locataire non trouvé")
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur interne",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Erreur lors de la récupération des paiements"),
 *             @OA\Property(property="error", type="string", example="Message d'erreur détaillé")
 *         )
 *     )
 * )
 */
public function index($locataireId)
{
    Log::info('Début de la méthode index - Liste des paiements', ['locataire_id' => $locataireId]);

    try {
        // Récupérer le locataire avec ses paiements et son bien
        Log::info('Recherche du locataire avec relations');
        $locataire = Locataire::with(['paiements', 'bien'])->findOrFail($locataireId);
        
        Log::info('Locataire trouvé', [
            'locataire_id' => $locataire->id,
            'nombre_paiements' => $locataire->paiements->count()
        ]);

        // Formater les dates en français (si nécessaire)
        Log::info('Formatage des dates des paiements');
        $locataire->paiements->transform(function ($paiement) {
            $paiement->created_at_formatted = Carbon::parse($paiement->created_at)->translatedFormat('d F Y');
            return $paiement;
        });

        Log::info('Liste des paiements récupérée avec succès');

        return response()->json([
            'data' => [
                'locataire' => $locataire,
                'message' => 'Paiements récupérés avec succès'
            ]
        ], 200);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        Log::error('Locataire non trouvé pour liste des paiements', [
            'locataire_id' => $locataireId,
            'error' => $e->getMessage()
        ]);
        return response()->json([
            'message' => 'Locataire non trouvé'
        ], 404);
    } catch (\Exception $e) {
        Log::error('Erreur lors de la récupération des paiements', [
            'locataire_id' => $locataireId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération des paiements',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * @OA\Get(
 *     path="/api/tenant/paiements/{id}",
 *     summary="Afficher les détails d'un paiement spécifique",
 *     description="Récupère les détails complets d'un paiement spécifique avec les informations du locataire et du bien associé",
 *     tags={"Paiements"},
 *     security={{"bearerAuth": {}}},
 *     
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID du paiement",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     
 *     @OA\Response(
 *         response=200,
 *         description="Détails du paiement récupérés avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Détails du paiement récupérés avec succès"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 ref="#/components/schemas/PaiementDetails"
 *             )
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=404,
 *         description="Paiement non trouvé",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Paiement non trouvé")
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur interne",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Erreur lors de la récupération du paiement"),
 *             @OA\Property(property="error", type="string", example="Message d'erreur détaillé")
 *         )
 *     )
 * )
 */
public function show($id)
{
    Log::info('Début de la méthode show - Détails paiement', ['paiement_id' => $id]);

    try {
        Log::info('Recherche du paiement avec relations');
        $paiement = Paiement::with([
            'locataire:id,name,prenom,email,contact',
            'bien:id,commune,type',
        ])->findOrFail($id);

        Log::info('Paiement trouvé', [
            'paiement_id' => $paiement->id,
            'locataire_id' => $paiement->locataire_id,
            'montant' => $paiement->montant
        ]);

        return response()->json([
            'success' => true,
            'data' => $paiement,
            'message' => 'Détails du paiement récupérés avec succès'
        ]);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        Log::error('Paiement non trouvé', [
            'paiement_id' => $id,
            'error' => $e->getMessage()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Paiement non trouvé'
        ], 404);
    } catch (\Exception $e) {
        Log::error('Erreur lors de la récupération du paiement', [
            'paiement_id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération du paiement',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * @OA\Get(
 *     path="/tenant/paiement/mon-qr-code",
 *     summary="Récupérer le QR code de paiement actif du locataire",
 *     description="Retourne le dernier code QR de vérification non utilisé et non expiré pour le locataire authentifié",
 *     tags={"Locataire - Paiements"},
 *     security={{"bearerAuth": {}}},
 *     
 *     @OA\Response(
 *         response=200,
 *         description="QR code récupéré avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="code", type="string", example="ABC123XYZ", description="Code de vérification"),
 *                 @OA\Property(property="expires_at", type="string", format="date-time", example="2024-12-31T23:59:59Z", description="Date d'expiration du code"),
 *                 @OA\Property(property="expires_in", type="string", example="48 heures", description="Temps restant avant expiration"),
 *                 @OA\Property(property="montant_total", type="number", format="float", example=1500.00, description="Montant total à payer"),
 *                 @OA\Property(property="nombre_mois", type="integer", example=3, description="Nombre de mois couverts"),
 *                 @OA\Property(property="mois_couverts", type="string", example="Janvier, Février, Mars", description="Mois couverts par le paiement"),
 *                 @OA\Property(property="qr_code_url", type="string", format="uri", nullable=true, example="https://example.com/storage/qr-codes/abc123.png", description="URL du QR code image"),
 *                 @OA\Property(property="is_valid", type="boolean", example=true, description="Indique si le code est toujours valide")
 *             )
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=403,
 *         description="Accès non autorisé",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Accès réservé aux locataires")
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=404,
 *         description="Aucun code QR trouvé",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Aucun code QR actif trouvé")
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=401,
 *         description="Non authentifié",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated")
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur interne",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Erreur interne du serveur"),
 *             @OA\Property(property="error", type="string", example="Message d'erreur détaillé")
 *         )
 *     )
 * )
 */
public function getMyQrCode(Request $request)
{
    Log::info('Début de la méthode getMyQrCode');

    // Récupérer le locataire authentifié
    $locataire = Auth::guard('sanctum')->user();
    
    Log::info('Utilisateur authentifié', [
        'user_type' => get_class($locataire),
        'user_id' => $locataire->id ?? 'non authentifié'
    ]);
    
    // Vérifier que l'utilisateur est bien un locataire
    if (!$locataire instanceof Locataire) {
        Log::warning('Accès non autorisé à getMyQrCode', [
            'user_type' => get_class($locataire)
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Accès réservé aux locataires'
        ], 403);
    }

    Log::info('Recherche du QR code actif pour le locataire', ['locataire_id' => $locataire->id]);

    // Récupérer le dernier code de vérification non utilisé et non expiré
    $qrCode = CashVerificationCode::where('locataire_id', $locataire->id)
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->where('is_archived', false)
                ->latest()
                ->first();

    if (!$qrCode) {
        Log::warning('Aucun QR code actif trouvé', ['locataire_id' => $locataire->id]);
        return response()->json([
            'success' => false,
            'message' => 'Aucun code QR actif trouvé'
        ], 404);
    }

    Log::info('QR code trouvé', [
        'qr_code_id' => $qrCode->id,
        'code' => $qrCode->code,
        'expires_at' => $qrCode->expires_at
    ]);

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
 * @OA\Post(
 *     path="/api/cinetpay/notify",
 *     summary="Gérer les notifications de paiement CinetPay",
 *     description="Endpoint de callback pour recevoir et traiter les notifications de paiement de CinetPay",
 *     tags={"Paiements - Webhooks"},
 *     
 *     @OA\RequestBody(
 *         required=true,
 *         description="Données de notification CinetPay",
 *         @OA\JsonContent(
 *             required={"cpm_trans_id", "cpm_amount", "cpm_currency", "cpm_result", "cpm_trans_date"},
 *             @OA\Property(property="cpm_site_id", type="string", example="859043", description="ID du site CinetPay"),
 *             @OA\Property(property="cpm_trans_id", type="string", example="PAY_1703500000000", description="ID de transaction unique"),
 *             @OA\Property(property="cpm_trans_date", type="string", example="20231215103000", description="Date de la transaction"),
 *             @OA\Property(property="cpm_amount", type="number", format="float", example=75000, description="Montant de la transaction"),
 *             @OA\Property(property="cpm_currency", type="string", example="XOF", description="Devise de la transaction"),
 *             @OA\Property(property="cpm_result", type="string", example="00", description="Résultat du paiement (00: succès)"),
 *             @OA\Property(property="signature", type="string", description="Signature de vérification"),
 *             @OA\Property(property="payment_method", type="string", example="MOBILE_MONEY", description="Méthode de paiement utilisée"),
 *             @OA\Property(property="cel_phone_num", type="string", example="+2250700000000", description="Numéro de téléphone du payeur"),
 *             @OA\Property(property="cpm_phone_prefixe", type="string", example="+225", description="Préfixe téléphonique"),
 *             @OA\Property(property="cpm_language", type="string", example="fr", description="Langue utilisée"),
 *             @OA\Property(property="cpm_version", type="string", example="1.0", description="Version de l'API"),
 *             @OA\Property(property="cpm_payment_config", type="string", example="SINGLE", description="Configuration du paiement"),
 *             @OA\Property(property="cpm_page_action", type="string", example="PAYMENT", description="Action de la page"),
 *             @OA\Property(property="cpm_custom", type="string", description="Champ personnalisé"),
 *             @OA\Property(property="cpm_designation", type="string", example="Paiement loyer", description="Désignation du paiement"),
 *             @OA\Property(property="cpm_error_message", type="string", description="Message d'erreur éventuel"),
 *             @OA\Property(property="cpm_operation_id", type="string", example="OP123456", description="ID de l'opération CinetPay")
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=200,
 *         description="Notification traitée avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Paiement enregistré")
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=400,
 *         description="Session de paiement invalide",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Session de paiement invalide")
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=409,
 *         description="Mois déjà payé",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Mois déjà payé")
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur interne",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Erreur interne du serveur")
 *         )
 *     )
 * )
 */
public function handleCinetPayNotification(Request $request)
{
    Log::info('=== DÉBUT Notification CinetPay reçue ===', $request->all());

    try {
        // Validation des données
        $request->validate([
            'cpm_trans_id' => 'required|string',
            'cpm_amount' => 'required|numeric',
            'cpm_currency' => 'required|string',
            'cpm_result' => 'required|string',
            'cpm_trans_date' => 'required|string',
            'signature' => 'required|string',
        ]);

        // Vérification de la signature
        $cinetPayService = new CinetPayService();
        $isValidSignature = $cinetPayService->verifySignature($request->all(), $request->signature);

        if (!$isValidSignature) {
            Log::error('Signature CinetPay invalide', ['transaction_id' => $request->cpm_trans_id]);
            return response()->json(['status' => 'error', 'message' => 'Signature invalide'], 400);
        }

        // Récupérer la session de paiement
        $paiementSession = PaiementSession::where('transaction_id', $request->cpm_trans_id)
            ->where('expires_at', '>', now())
            ->first();

        if (!$paiementSession) {
            Log::error('Session de paiement non trouvée', ['transaction_id' => $request->cpm_trans_id]);
            return response()->json(['status' => 'error', 'message' => 'Session de paiement invalide'], 400);
        }

        // Vérification si le paiement existe déjà et mise à jour du statut
        $existingPayment = Paiement::where('transaction_id', $request->cpm_trans_id)->first();
        if ($existingPayment) {
            $nouveauStatut = $this->mapCinetPayStatus($request->cpm_result);
            $existingPayment->update(['statut' => $nouveauStatut]);
            return response()->json(['status' => 'success', 'message' => 'Statut mis à jour']);
        }

        // Créer le paiement s'il n'existe pas déjà
        $paiement = Paiement::create([
            'montant' => $paiementSession->montant,
            'mois_couvert' => $paiementSession->mois_couvert,
            'methode_paiement' => 'Mobile Money',
            'statut' => $this->mapCinetPayStatus($request->cpm_result),
            'transaction_id' => $request->cpm_trans_id,
            // Ajoutez d'autres champs nécessaires ici
        ]);

        // Supprimer la session de paiement
        $paiementSession->delete();

        return response()->json(['status' => 'success', 'message' => 'Paiement enregistré']);
    } catch (\Exception $e) {
        Log::error('Erreur lors du traitement de la notification', ['error' => $e->getMessage()]);
        return response()->json(['status' => 'error', 'message' => 'Erreur interne du serveur'], 500);
    }
}
/**
 * Mapper les statuts CinetPay vers les statuts de l'application
 */
private function mapCinetPayStatus($cinetPayStatus)
{
    Log::debug('Mapping statut CinetPay', ['cinetpay_status' => $cinetPayStatus]);
    
    $statusMap = [
        '00' => 'payé',           // Paiement réussi
        '01' => 'échoué',         // Paiement refusé
        '02' => 'en_attente',     // Paiement en attente
        '03' => 'annulé',         // Paiement annulé
        '04' => 'en_attente',     // Paiement en cours
    ];

    $result = $statusMap[$cinetPayStatus] ?? 'en_attente';
    Log::debug('Statut mappé', ['result' => $result]);
    
    return $result;
}

/**
 * @OA\Get(
 *     path="/api/cinetpay/return",
 *     summary="Gérer le retour de paiement CinetPay via URL",
 *     description="Endpoint pour traiter le retour de paiement après redirection depuis CinetPay",
 *     tags={"Paiements - Callbacks"},
 *     
 *     @OA\Parameter(
 *         name="cpm_trans_id",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="string", example="PAY_1703500000000")
 *     ),
 *     @OA\Parameter(
 *         name="cpm_result",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="string", example="00")
 *     ),
 *     @OA\Parameter(
 *         name="cpm_amount",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="number", format="float", example=75000)
 *     ),
 *     @OA\Parameter(
 *         name="cel_phone_num",
 *         in="query",
 *         @OA\Schema(type="string", example="+2250700000000")
 *     ),
 *     @OA\Parameter(
 *         name="signature",
 *         in="query",
 *         @OA\Schema(type="string")
 *     ),
 *     
 *     @OA\Response(
 *         response=200,
 *         description="Retour de paiement traité avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Paiement traité avec succès"),
 *             @OA\Property(property="statut", type="string", example="payé"),
 *             @OA\Property(property="transaction_id", type="string", example="PAY_1703500000000")
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=400,
 *         description="Données manquantes ou invalides",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Données de paiement manquantes")
 *         )
 *     )
 * )
 */
public function handleCinetPayReturn(Request $request)
{
    Log::info('=== DÉBUT Retour CinetPay via URL ===', $request->all());

    try {
        // Validation des paramètres requis
        $request->validate([
            'cpm_trans_id' => 'required|string',
            'cpm_result' => 'required|string',
            'cpm_amount' => 'required|numeric',
        ]);

        // Récupération de la session de paiement
        $paiementSession = PaiementSession::where('transaction_id', $request->cpm_trans_id)
            ->where('expires_at', '>', now())
            ->first();

        if (!$paiementSession) {
            Log::error('Session de paiement non trouvée pour le retour', ['transaction_id' => $request->cpm_trans_id]);
            return redirect()->route('error.page')->with('message', 'Session de paiement non trouvée');
        }

        // Vérifiez si le paiement existe déjà
        $existingPayment = Paiement::where('transaction_id', $request->cpm_trans_id)->first();
        if (!$existingPayment) {
            // Créez le paiement
            $paiement = Paiement::create([
                'montant' => $paiementSession->montant,
                'mois_couvert' => $paiementSession->mois_couvert,
                'methode_paiement' => 'Mobile Money',
                'statut' => $this->mapCinetPayStatus($request->cpm_result),
                'transaction_id' => $request->cpm_trans_id,
                // Ajoutez d'autres champs nécessaires ici
            ]);
        } else {
            // Mettez à jour le statut si nécessaire
            $nouveauStatut = $this->mapCinetPayStatus($request->cpm_result);
            if ($existingPayment->statut !== $nouveauStatut) {
                $existingPayment->update(['statut' => $nouveauStatut]);
            }
        }

        // Supprimez la session de paiement
        $paiementSession->delete();

        // Redirigez l'utilisateur vers une page de succès
        return redirect()->route('success.page')->with('message', 'Paiement traité avec succès');
    } catch (\Exception $e) {
        Log::error('Erreur lors du traitement du retour CinetPay', ['error' => $e->getMessage()]);
        return redirect()->route('error.page')->with('message', 'Erreur lors du traitement du paiement');
    }
}

/**
 * Construire la réponse de redirection
 */
private function buildRedirectResponse($transactionId, $status, $message)
{
    // URL de votre application mobile avec les paramètres de statut
    $appUrl = config('app.mobile_deeplink_url', 'yourapp://paiement/result');
    
    $redirectUrl = $appUrl . '?' . http_build_query([
        'transaction_id' => $transactionId,
        'status' => $status,
        'message' => $message,
        'timestamp' => now()->timestamp
    ]);

    Log::info('Redirection vers application', [
        'redirect_url' => $redirectUrl,
        'status' => $status,
        'message' => $message
    ]);

    // Pour une API, retourner les données JSON
    return response()->json([
        'success' => $status === 'success',
        'message' => $message,
        'statut' => $status,
        'transaction_id' => $transactionId,
        'redirect_url' => $redirectUrl
    ]);
}

/**
 * @OA\Get(
 *     path="/api/paiement/check-status/{transactionId}",
 *     summary="Vérifier le statut d'un paiement",
 *     description="Vérifie le statut d'un paiement en utilisant l'ID de transaction",
 *     tags={"Paiements"},
 *     security={{"bearerAuth": {}}},
 *     
 *     @OA\Parameter(
 *         name="transactionId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="string", example="PAY_1703500000000")
 *     ),
 *     
 *     @OA\Response(
 *         response=200,
 *         description="Statut du paiement récupéré avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="transaction_id", type="string", example="PAY_1703500000000"),
 *             @OA\Property(property="statut", type="string", example="payé"),
 *             @OA\Property(property="date_paiement", type="string", format="date-time", example="2023-12-15 10:30:00"),
 *             @OA\Property(property="montant", type="number", format="float", example=75000)
 *         )
 *     ),
 *     
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
public function checkPaymentStatus($transactionId)
{
    Log::info('Vérification statut paiement', ['transaction_id' => $transactionId]);

    try {
        // Vérifier d'abord en base de données
        $paiement = Paiement::where('transaction_id', $transactionId)->first();

        if (!$paiement) {
            Log::warning('Paiement non trouvé en base', ['transaction_id' => $transactionId]);
            return response()->json([
                'success' => false,
                'message' => 'Paiement non trouvé'
            ], 404);
        }

        // Si le paiement est déjà marqué comme payé, retourner le statut
        if ($paiement->statut === 'payé') {
            return response()->json([
                'success' => true,
                'transaction_id' => $paiement->transaction_id,
                'statut' => $paiement->statut,
                'date_paiement' => $paiement->date_paiement,
                'montant' => $paiement->montant,
                'reference' => $paiement->reference
            ]);
        }

        // Vérifier auprès de CinetPay pour les statuts en attente
        $cinetPayService = new CinetPayService();
        $statusCheck = $cinetPayService->checkPaymentStatus($transactionId);

        if ($statusCheck && isset($statusCheck['data'])) {
            $cinetpayStatus = $statusCheck['data']['status'] ?? null;
            
            if ($cinetpayStatus) {
                $nouveauStatut = $this->mapCinetPayStatus($cinetpayStatus);
                
                // Mettre à jour le statut si nécessaire
                if ($paiement->statut !== $nouveauStatut) {
                    $paiement->update([
                        'statut' => $nouveauStatut,
                        'date_paiement' => $nouveauStatut === 'payé' ? now() : $paiement->date_paiement,
                    ]);
                    
                    Log::info('Statut mis à jour depuis CinetPay', [
                        'transaction_id' => $transactionId,
                        'ancien_statut' => $paiement->statut,
                        'nouveau_statut' => $nouveauStatut
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'transaction_id' => $paiement->transaction_id,
            'statut' => $paiement->statut,
            'date_paiement' => $paiement->date_paiement,
            'montant' => $paiement->montant,
            'reference' => $paiement->reference,
            'last_checked' => now()->toISOString()
        ]);

    } catch (\Exception $e) {
        Log::error('Erreur vérification statut paiement', [
            'transaction_id' => $transactionId,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la vérification du statut'
        ], 500);
    }
}
}