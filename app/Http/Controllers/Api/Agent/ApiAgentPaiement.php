<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use App\Models\CashVerificationCode;
use App\Models\Locataire;
use App\Models\Paiement;
use Carbon\Carbon;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Client\Response;
use Twilio\Rest\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


/**
 * @OA\Tag(
 *     name="Agent - Paiements",
 *     description="Endpoints pour la gestion des paiements par les agents"
 * )
 */
/**
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         example="Une erreur s'est produite"
 *     ),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         example={"field": {"Erreur de validation"}}
 *     )
 * )
 */
class ApiAgentPaiement extends Controller
{
     /**
     * @OA\Get(
     *     path="/api/agent/paiements/history",
     *     operationId="getPaymentHistory",
     *     tags={"Agent - Paiements"},
     *     summary="Historique des paiements",
     *     description="Récupère l'historique des paiements gérés par l'agent connecté",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", example=10),
     *         description="Nombre d'éléments par page"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Historique récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Paiement")
     *             ),
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=100),
     *                 @OA\Property(property="last_page", type="integer", example=10),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="to", type="integer", example=10)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Non authentifié"),
     *             @OA\Property(property="message", type="string", example="Utilisateur non connecté")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Erreur serveur"),
     *             @OA\Property(property="message", type="string", example="Message d'erreur détaillé")
     *         )
     *     )
     * )
     */
    public function historyApi(Request $request)
    {
        try {
            Carbon::setLocale('fr');
            
            // Vérifier que l'utilisateur est authentifié
            if (!Auth::guard('sanctum')->check()) {
                return response()->json([
                    'error' => 'Non authentifié',
                    'message' => 'Utilisateur non connecté'
                ], 401);
            }

            $comptableId = Auth::guard('sanctum')->user()->id;
            $paiements = Paiement::where('comptable_id', $comptableId)
                ->paginate($request->get('per_page', 10));

            return response()->json([
                'success' => true,
                'data' => $paiements->items(),
                'pagination' => [
                    'current_page' => $paiements->currentPage(),
                    'per_page' => $paiements->perPage(),
                    'total' => $paiements->total(),
                    'last_page' => $paiements->lastPage(),
                    'from' => $paiements->firstItem(),
                    'to' => $paiements->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur serveur',
                'message' => $e->getMessage()
            ], 500);
        }
    }

     
    /**
     * @OA\Get(
     *     path="/api/agent/locataires/retard",
     *     operationId="getLocatairesRetard",
     *     tags={"Agent - Paiements"},
     *     summary="Locataires en retard de paiement",
     *     description="Récupère la liste des locataires en retard de paiement pour le mois courant",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example="1"),
     *                     @OA\Property(property="name", type="string", example="Dupont"),
     *                     @OA\Property(property="prenom", type="string", example="Jean"),
     *                     @OA\Property(property="email", type="string", example="jean.dupont@email.com"),
     *                     @OA\Property(property="contact", type="string", example="+33123456789")
     *                 )
     *             ),
     *             @OA\Property(property="count", type="integer", example=5)
     *         )
     *     )
     * )
     */
    public function getLocatairesRetard(Request $request)
    {
        Carbon::setLocale('fr');
        $comptable = Auth::guard('sanctum')->user();
        $currentMonth = now()->format('Y-m');
        
        if($comptable->agence_id || $comptable->proprietaire_id){
            $latePayers = Locataire::where(function($query) use ($comptable) {
                    if ($comptable->agence_id) {
                        $query->orWhere('agence_id', $comptable->agence_id);
                    }
                    elseif($comptable->proprietaire_id) {
                        $query->orWhere('proprietaire_id', $comptable->proprietaire_id);
                    }
                })
                ->where('status', 'Actif')
                ->whereDoesntHave('paiements', function($query) use ($currentMonth) {
                    $query->where('mois_couvert', $currentMonth)
                        ->where('statut', 'payé');
                })
                ->select('name', 'prenom', 'email', 'contact')
                ->get();
        } else {
            $latePayers = Locataire::whereNull('agence_id')
                ->whereNull('proprietaire_id')
                ->where('status', 'Actif')
                ->whereDoesntHave('paiements', function($query) use ($currentMonth) {
                    $query->where('mois_couvert', $currentMonth)
                        ->where('statut', 'payé');
                })
                ->select('id','name', 'prenom', 'email', 'contact')
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $latePayers,
            'count' => $latePayers->count()
        ]);
    }

     /**
     * @OA\Get(
     *     path="/api/agent/locataires/a-jour",
     *     operationId="getLocatairesAJour",
     *     tags={"Agent - Paiements"},
     *     summary="Locataires à jour de paiement",
     *     description="Récupère la liste des locataires à jour de paiement pour le mois courant",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example="1"),
     *                     @OA\Property(property="name", type="string", example="Martin"),
     *                     @OA\Property(property="prenom", type="string", example="Sophie"),
     *                     @OA\Property(property="email", type="string", example="sophie.martin@email.com"),
     *                     @OA\Property(property="contact", type="string", example="+33123456790")
     *                 )
     *             ),
     *             @OA\Property(property="count", type="integer", example=8)
     *         )
     *     )
     * )
     */
    public function getLocatairesAJour(Request $request)
    {
        $comptable = Auth::guard('sanctum')->user();
        $currentMonth = now()->format('Y-m');
        
        if($comptable->agence_id || $comptable->proprietaire_id){
            $upToDateLocataires = Locataire::where(function($query) use ($comptable) {
                    if ($comptable->agence_id) {
                        $query->orWhere('agence_id', $comptable->agence_id);
                    }
                    if ($comptable->proprietaire_id) {
                        $query->orWhere('proprietaire_id', $comptable->proprietaire_id);
                    }
                })
                ->where('status', 'Actif')
                ->whereHas('paiements', function($query) use ($currentMonth) {
                    $query->where('mois_couvert', $currentMonth)
                          ->where('statut', 'payé');
                })
                ->select('id','name', 'prenom', 'email', 'contact')
                ->get();
        } else {
            $upToDateLocataires = Locataire::whereNull('agence_id')
                ->whereNull('proprietaire_id')
                ->where('status', 'Actif')
                ->whereHas('paiements', function($query) use ($currentMonth) {
                    $query->where('mois_couvert', $currentMonth)
                          ->where('statut', 'payé');
                })
                ->select('name', 'prenom', 'email', 'contact')
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $upToDateLocataires,
            'count' => $upToDateLocataires->count()
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/agent/locataires/en-attente",
     *     operationId="getLocatairesEnAttente",
     *     tags={"Agent - Paiements"},
     *     summary="Locataires avec paiement en attente",
     *     description="Récupère la liste des locataires avec des paiements en statut d'attente",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example="1"),
     *                     @OA\Property(property="name", type="string", example="Dubois"),
     *                     @OA\Property(property="prenom", type="string", example="Pierre"),
     *                     @OA\Property(property="email", type="string", example="pierre.dubois@email.com"),
     *                     @OA\Property(property="contact", type="string", example="+33123456791")
     *                 )
     *             ),
     *             @OA\Property(property="count", type="integer", example=3)
     *         )
     *     )
     * )
     */
    public function getLocatairesEnAttente(Request $request)
    {
        $comptable = Auth::guard('sanctum')->user();
        $currentMonth = now()->format('Y-m');
        
        if($comptable->agence_id || $comptable->proprietaire_id){
            $pendingLocataires = Locataire::where(function($query) use ($comptable) {
                    if ($comptable->agence_id) {
                        $query->orWhere('agence_id', $comptable->agence_id);
                    }
                    if ($comptable->proprietaire_id) {
                        $query->orWhere('proprietaire_id', $comptable->proprietaire_id);
                    }
                })
                ->where('status', 'Actif')
                ->whereHas('paiements', function($query) use ($currentMonth) {
                    $query->where('mois_couvert', $currentMonth)
                          ->where('statut', '!=', 'payé');
                })
                ->select('id','name', 'prenom', 'email', 'contact')
                ->get();
        } else {
            $pendingLocataires = Locataire::whereNull('agence_id')
                ->whereNull('proprietaire_id')
                ->where('status', 'Actif')
                ->whereHas('paiements', function($query) use ($currentMonth) {
                    $query->where('mois_couvert', $currentMonth)
                          ->where('statut', '!=', 'payé');
                })
                ->select('name', 'prenom', 'email', 'contact')
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $pendingLocataires,
            'count' => $pendingLocataires->count()
        ]);
    }

     /**
     * @OA\Get(
     *     path="/api/agent/locataire/{id}/details",
     *     operationId="getLocataireDetails",
     *     tags={"Agent - Paiements"},
     *     summary="Détails d'un locataire",
     *     description="Récupère les détails d'un locataire avec ses informations de paiement",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID du locataire"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="locataire",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nom", type="string", example="Dupont"),
     *                     @OA\Property(property="prenom", type="string", example="Jean"),
     *                     @OA\Property(property="email", type="string", example="jean.dupont@email.com"),
     *                     @OA\Property(property="telephone", type="string", example="+33123456789")
     *                 ),
     *                 @OA\Property(
     *                     property="bien",
     *                     type="object",
     *                     @OA\Property(property="type", type="string", example="Appartement"),
     *                     @OA\Property(property="commune", type="string", example="Paris 15e"),
     *                     @OA\Property(property="prix", type="number", format="float", example=750.50)
     *                 ),
     *                 @OA\Property(
     *                     property="prochain_mois_a_payer",
     *                     type="object",
     *                     @OA\Property(property="mois_couvert", type="string", example="2024-01"),
     *                     @OA\Property(property="mois_couvert_display", type="string", example="janvier 2024"),
     *                     @OA\Property(property="annee", type="integer", example=2024),
     *                     @OA\Property(property="mois_numero", type="integer", example=1),
     *                     @OA\Property(property="montant", type="number", format="float", example=750.50),
     *                     @OA\Property(property="deja_paye", type="boolean", example=false)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Locataire non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Locataire non trouvé ou non autorisé")
     *         )
     *     )
     * )
     */

    public function getLocataireDetails($id)
    {
         $comptable = Auth::guard('sanctum')->user();
        
        // Récupérer le locataire avec son bien
        $locataire = Locataire::with('bien')
            ->where('id', $id)
            ->where(function($query) use ($comptable) {
                if ($comptable->agence_id) {
                    $query->where('agence_id', $comptable->agence_id);
                }
                if ($comptable->proprietaire_id) {
                    $query->orWhere('proprietaire_id', $comptable->proprietaire_id);
                }
            })
            ->first();

        if (!$locataire) {
            return response()->json([
                'success' => false,
                'message' => 'Locataire non trouvé ou non autorisé'
            ], 404);
        }

        // Trouver le dernier mois payé
        $dernierPaiement = Paiement::where('locataire_id', $locataire->id)
            ->where('statut', 'payé')
            ->orderBy('mois_couvert', 'desc')
            ->first();

        // Déterminer le mois à payer
        if ($dernierPaiement) {
            $moisAPayer = Carbon::parse($dernierPaiement->mois_couvert)->addMonth();
        } else {
            // Si aucun paiement, utiliser la date d'entrée ou le mois courant
            $moisAPayer = $locataire->date_entree 
                ? Carbon::parse($locataire->date_entree)
                : Carbon::now();
        }

        // Vérifier si le mois à payer n'a pas déjà été payé
        $paiementExistant = Paiement::where('locataire_id', $locataire->id)
            ->where('mois_couvert', $moisAPayer->format('Y-m'))
            ->where('statut', 'payé')
            ->exists();

        if ($paiementExistant) {
            // Si déjà payé, trouver le prochain mois non payé
            $moisAPayer = $this->trouverProchainMoisNonPaye($locataire->id, $moisAPayer);
        }

        // Calculer le montant à payer
        $montantAPayer = $locataire->bien->montant_majore ?? $locataire->bien->prix;

        // Formater les données de réponse
        $response = [
            'locataire' => [
                'id' => $locataire->id,
                'nom' => $locataire->name,
                'prenom' => $locataire->prenom,
                'email' => $locataire->email,
                'telephone' => $locataire->contact
            ],
            'bien' => $locataire->bien ? [
                'type' => $locataire->bien->type,
                'commune' => $locataire->bien->commune,
                'prix' => $locataire->bien->montant_majore ?? $locataire->bien->prix
            ] : null,
            'prochain_mois_a_payer' => [
                'mois_couvert' => $moisAPayer->format('Y-m'),
                'mois_couvert_display' => $moisAPayer->translatedFormat('F Y'),
                'annee' => $moisAPayer->year,
                'mois_numero' => $moisAPayer->month,
                'montant' => $montantAPayer,
                'deja_paye' => false
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $response
        ]);
    }

        private function trouverProchainMoisNonPaye($locataireId, Carbon $dateDebut)
    {
        $dateCourante = $dateDebut->copy();
        $maintenant = Carbon::now();
        
        // Limiter la recherche à 12 mois maximum dans le futur
        $maxMois = 12;
        $compteur = 0;
        
        while ($compteur < $maxMois && $dateCourante <= $maintenant->addMonths(3)) {
            $moisFormatte = $dateCourante->format('Y-m');
            
            $dejaPaye = Paiement::where('locataire_id', $locataireId)
                ->where('mois_couvert', $moisFormatte)
                ->where('statut', 'payé')
                ->exists();
            
            if (!$dejaPaye) {
                return $dateCourante;
            }
            
            $dateCourante->addMonth();
            $compteur++;
        }
        
        // Si tous les mois sont payés, retourner le mois suivant le dernier trouvé
        return $dateCourante;
    }

    /**
     * @OA\Post(
     *     path="/api/agent/paiement/generer-code-especes",
     *     operationId="generateCashCode",
     *     tags={"Agent - Paiements"},
     *     summary="Générer un code de paiement en espèces",
     *     description="Génère un code unique pour le paiement en espèces avec QR code",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"locataire_id", "nombre_mois"},
     *             @OA\Property(property="locataire_id", type="integer", example=1, description="ID du locataire"),
     *             @OA\Property(property="nombre_mois", type="integer", example=1, description="Nombre de mois à payer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code généré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Code de paiement généré avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="ABC123"),
     *                
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Accès non autorisé à ce locataire")
     *         )
     *     ),
     *     @OA\Response(
     *            response=422,
    *         description="Erreur de validation",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(
    *                 property="message",
    *                 type="string",
    *                 example="Les données fournies sont invalides"
    *             ),
    *             @OA\Property(
    *                 property="errors",
    *                 type="object",
    *                 example={"champ": {"Le champ est requis"}}
    *             )
    *         )
    *     )
     * )
     */
    public function generateCashCode(Request $request)
{
    $request->validate([
        'locataire_id' => 'required|exists:locataires,id',
        'nombre_mois' => 'required|integer|min:1'
    ]);

    $comptable = Auth::guard('sanctum')->user();
    $locataire = Locataire::with('bien')->findOrFail($request->locataire_id);
    
    // Vérifier l'accès du comptable
    if (($comptable->agence_id && $locataire->agence_id != $comptable->agence_id) ||
        ($comptable->proprietaire_id && $locataire->proprietaire_id != $comptable->proprietaire_id)) {
        return response()->json([
            'success' => false,
            'message' => 'Accès non autorisé à ce locataire'
        ], 403);
    }

    // Nettoyer les données du locataire
    $locataireData = [
        'id' => $locataire->id,
        'nom' => $this->cleanUtf8($locataire->name),
        'prenom' => $this->cleanUtf8($locataire->prenom),
        'email' => $locataire->email,
        'telephone' => $locataire->contact
    ];

    // Nettoyer les données du bien
    $bienData = $locataire->bien ? [
        'type' => $this->cleanUtf8($locataire->bien->type),
        'commune' => $this->cleanUtf8($locataire->bien->commune),
        'prix' => $locataire->bien->montant_majore ?? $locataire->bien->prix,
    ] : null;

    // Générer un code aléatoire de 6 caractères
    $code = Str::upper(Str::random(6));
    
    // Calculer le montant total
    $montantParMois = $locataire->bien->montant_majore ?? $locataire->bien->prix;
    $montantTotal = $montantParMois * $request->nombre_mois;

    // Déterminer les mois couverts (nettoyer les données)
    $moisCouverts = [];
    $moisCouvertsDisplay = [];
    $dateActuelle = now();
    
    for ($i = 0; $i < $request->nombre_mois; $i++) {
        $currentDate = $dateActuelle->copy()->addMonths($i);
        $moisCouverts[] = $currentDate->format('Y-m');
        $moisCouvertsDisplay[] = $this->cleanUtf8($currentDate->translatedFormat('F Y'));
    }
    
    $moisCouvertsStr = implode(', ', $moisCouvertsDisplay);

    // Générer le QR code
    try {
        $options = new QROptions([
            'version' => 10,
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_L,
            'scale' => 5,
            'imageBase64' => false,
            'quietzoneSize' => 2,
        ]);

        $qrcode = (new QRCode($options))->render($code);
        $qrCodePath = 'qrcodes/cash_payments/' . $code . '.png';
        Storage::disk('public')->put($qrCodePath, base64_decode($qrcode));
    } catch (\Exception $e) {
        Log::error('Erreur génération QR code: ' . $e->getMessage());
        $qrCodePath = null;
        $qrcode = null;
    }

    // Créer ou mettre à jour le code
    $cashCode = CashVerificationCode::updateOrCreate(
        ['locataire_id' => $locataire->id],
        [
            'code' => $code,
            'expires_at' => now()->addHours(24),
            'nombre_mois' => $request->nombre_mois,
            'mois_couverts' => $moisCouvertsStr,
            'montant_total' => $montantTotal,
            'is_archived' => false,
            'used_at' => null,
            'paiement_id' => null,
            'qr_code_path' => $qrCodePath,
            'comptable_id' => $comptable->id
        ]
    );

    // Préparer la réponse avec des données nettoyées
    $responseData = [
        'success' => true,
        'message' => 'Code de paiement généré avec succès',
        'data' => [
            'code' => $code,
            'locataire' => $locataireData,
            'bien' => $bienData,
            'details_paiement' => [
                'nombre_mois' => $request->nombre_mois,
                'montant_par_mois' => $montantParMois,
                'montant_total' => $montantTotal,
                'mois_couverts' => $moisCouverts,
                'mois_couverts_display' => $moisCouvertsDisplay,
                'expiration' => now()->addHours(24)->toIso8601String()
            ],
            'qr_code' => [
                'url' => $qrCodePath ? Storage::url($qrCodePath) : null,
                'base64' => $qrcode
            ]
        ]
    ];

    // Envoyer les notifications (email et SMS)
    $this->sendPaymentCodeNotifications($locataire, $code, $montantTotal, $moisCouvertsStr, $qrCodePath);

    // Retourner la réponse avec encodage JSON forcé
    return response()->json($responseData, 200, [
        'Content-Type' => 'application/json; charset=UTF-8'
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
}

/**
 * Nettoyer les chaînes UTF-8
 */
private function cleanUtf8($string)
{
    if (!is_string($string)) {
        return $string;
    }
    
    // Nettoyer les caractères UTF-8 mal formés
    $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
    
    // Supprimer les caractères non-UTF-8
    $string = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $string);
    
    return $string;
}

/**
 * Envoyer les notifications (email et SMS)
 */
private function sendPaymentCodeNotifications($locataire, $code, $montantTotal, $moisCouvertsStr, $qrCodePath)
{
    // Email
    try {
        Mail::to($locataire->email)->send(new \App\Mail\CashPaymentCodeMail(
            $code, 
            $locataire,
            $montantTotal,
            $moisCouvertsStr,
            $qrCodePath ? Storage::url($qrCodePath) : null
        ));
    } catch (\Exception $e) {
        Log::error("Erreur envoi email code cash: " . $e->getMessage());
    }

    // SMS
    try {
        $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
        
        $phoneNumber = $this->formatPhoneNumberForSms($locataire->telephone);
        $smsContent = "Bonjour {$this->cleanUtf8($locataire->prenom)},\n\n"
                    . "Votre code de paiement cash: {$code}\n"
                    . "Montant: {$montantTotal} FCFA\n"
                    . "Mois: {$this->cleanUtf8($moisCouvertsStr)}\n\n"
                    . "Ce code expire dans 24h.";

        $message = $twilio->messages->create(
            $phoneNumber,
            [
                'from' => env('TWILIO_PHONE_NUMBER'),
                'body' => $smsContent
            ]
        );

        Log::channel('sms')->info('SMS cash code envoyé', [
            'locataire_id' => $locataire->id,
            'code' => $code,
            'message_sid' => $message->sid
        ]);

    } catch (\Exception $e) {
        Log::channel('sms')->error('Erreur envoi SMS cash code', [
            'locataire_id' => $locataire->id,
            'error' => $e->getMessage()
        ]);
    }
}


    /**
     * Formater le numéro de téléphone pour SMS
     */
    private function formatPhoneNumberForSms($phone)
    {
        // Nettoyer le numéro
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Ajouter l'indicatif international si absent
        if (strpos($phone, '+') !== 0) {
            // Supposer que c'est un numéro français si pas d'indicatif
            if (strlen($phone) === 9 && substr($phone, 0, 1) === '0') {
                $phone = '+33' . substr($phone, 1);
            } elseif (strlen($phone) === 10 && substr($phone, 0, 2) === '00') {
                $phone = '+' . substr($phone, 2);
            }
        }
        
        return $phone;
    }

     /**
     * @OA\Post(
     *     path="/api/paiement/verifier-code-especes",
     *     operationId="verifyCashCodeAgent",
     *     tags={"Agent - Paiements"},
     *     summary="Vérifier un code de paiement en espèces",
     *     description="Vérifie et traite un code de paiement en espèces",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"locataire_id", "code"},
     *             @OA\Property(property="locataire_id", type="integer", example=1, description="ID du locataire"),
     *             @OA\Property(property="code", type="string", example="ABC123", description="Code de vérification"),
     *             @OA\Property(property="nombre_mois", type="integer", example=1, description="Nombre de mois à payer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paiement traité avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Paiement enregistré avec succès pour 1 mois: janvier 2024"),
     *      
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Code invalide ou mois déjà payés",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Code invalide ou expiré")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Une erreur est survenue lors du traitement du paiement")
     *         )
     *     )
     * )
     */
    public function verifyCashCodeAgent(Request $request)
    {
        $request->validate([
            'locataire_id' => 'required|exists:locataires,id',
            'code' => 'required|string|size:6',
            'nombre_mois' => 'sometimes|integer|min:1'
        ]);

        DB::beginTransaction();

        try {
            $locataire = Locataire::with('bien')->findOrFail($request->locataire_id);
            $nombreMois = $request->nombre_mois ?? 1;

            // Vérifier le code
            $codeValide = CashVerificationCode::where('locataire_id', $locataire->id)
                ->where('code', $request->code)
                ->where('expires_at', '>', now())
                ->whereNull('used_at')
                ->first();

            if (!$codeValide) {
                return response()->json([
                    'success' => false,
                    'message' => 'Code invalide ou expiré'
                ], 400);
            }

            // Utiliser le nombre de mois du code si disponible
            if ($codeValide->nombre_mois) {
                $nombreMois = $codeValide->nombre_mois;
            }

            // Déterminer le mois de départ
            $dernierPaiement = Paiement::where('locataire_id', $locataire->id)
                ->where('statut', 'payé')
                ->orderBy('mois_couvert', 'desc')
                ->first();

            $dateDebut = $dernierPaiement 
                ? Carbon::parse($dernierPaiement->mois_couvert)->addMonth()
                : now();

            // Préparer les mois à payer
            $moisAPayer = [];
            $moisDejaPayes = [];
            $currentDate = $dateDebut->copy();

            for ($i = 0; $i < $nombreMois; $i++) {
                $moisFormat = $currentDate->format('Y-m');
                
                $paiementExistant = Paiement::where('locataire_id', $locataire->id)
                    ->where('mois_couvert', $moisFormat)
                    ->where('statut', 'payé')
                    ->exists();

                if ($paiementExistant) {
                    $moisDejaPayes[] = $currentDate->translatedFormat('F Y');
                } else {
                    $moisAPayer[] = [
                        'mois' => $moisFormat,
                        'libelle' => $currentDate->translatedFormat('F Y')
                    ];
                }

                $currentDate->addMonth();
            }

            if (empty($moisAPayer)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tous les mois sélectionnés ont déjà été payés: ' . implode(', ', $moisDejaPayes)
                ], 400);
            }

            // Montant par mois
            $montantParMois = $locataire->bien->montant_majore ?? $locataire->bien->prix;
            $montantTotal = $montantParMois * count($moisAPayer);

            // Générer une référence de base
            $typePrefix = 'PAY-';
            do {
                $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
                $referenceBase = $typePrefix . $randomNumber;
            } while (Paiement::where('reference', 'like', $referenceBase . '%')->exists());

            // Enregistrement des paiements
            $paiementsCrees = [];
            foreach ($moisAPayer as $mois) {
                $paiement = Paiement::create([
                    'montant' => $montantParMois,
                    'date_paiement' => now(),
                    'mois_couvert' => $mois['mois'],
                    'methode_paiement' => 'Espèces',
                    'statut' => 'payé',
                    'reference' => $referenceBase . '-' . $mois['mois'],
                    'locataire_id' => $locataire->id,
                    'bien_id' => $locataire->bien_id,
                    'verif_espece' => $request->code,
                    'comptable_id' => Auth::guard('sanctum')->user()->id,
                    'nombre_mois' => $nombreMois
                ]);
                
                $paiementsCrees[] = $paiement;
            }

            // Marquer le code comme utilisé
            $codeValide->update([
                'used_at' => now(),
                'paiement_id' => $paiementsCrees[0]->id, // Lier au premier paiement
                'is_archived' => true
            ]);

            // Réinitialiser le montant majoré si nécessaire
            if ($locataire->bien->montant_majore) {
                $locataire->bien->update(['montant_majore' => null]);
            }

            DB::commit();

            // Message de confirmation
            $message = 'Paiement enregistré avec succès pour ' . count($moisAPayer) . ' mois: ' . 
                      implode(', ', array_column($moisAPayer, 'libelle'));
            
            if (!empty($moisDejaPayes)) {
                $message .= ' (Mois déjà payés: ' . implode(', ', $moisDejaPayes) . ')';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'montant_total' => $montantTotal,
                    'mois_payes' => array_column($moisAPayer, 'libelle'),
                    'mois_payes_details' => $moisAPayer,
                    'locataire' => [
                        'id' => $locataire->id,
                        'nom' => $locataire->nom,
                        'prenom' => $locataire->prenom
                    ],
                    'bien' => [
                        'id' => $locataire->bien->id,
                        'type' => $locataire->bien->type,
                        'commune' => $locataire->bien->commune
                    ],
                    'reference_paiement' => $referenceBase,
                    'nombre_mois_payes' => count($moisAPayer)
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de la vérification du code: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors du traitement du paiement',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
