<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContactMail;
use Illuminate\Http\Request;
use App\Models\Bien;
use App\Models\Proprietaire;
use App\Models\Agence;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * @OA\Info(
 *     title="API Immobilière",
 *     version="1.0.0",
 *     description="API pour la gestion des biens immobiliers"
 * )
 * 
 * @OA\Tag(
 *     name="Accueil",
 *     description="Endpoints principaux"
 * )
 * @OA\Tag(
 *     name="Biens",
 *     description="Gestion des biens immobiliers"
 * )
 * @OA\Tag(
 *     name="Contact",
 *     description="Envoi de messages de contact"
 * )
 * 
 * 
 * // ===================================================================
 * // DÉFINITIONS GLOBALES DES SCHÉMAS (MODÈLES)
 * // ===================================================================
 * 
 * @OA\Schema(
 *     schema="HomeResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(property="biens", ref="#/components/schemas/BienPagination"),
 *         @OA\Property(
 *             property="counts",
 *             type="object",
 *             @OA\Property(property="appartements", type="integer", example=15),
 *             @OA\Property(property="maisons", type="integer", example=8),
 *             @OA\Property(property="terrains", type="integer", example=5)
 *         ),
 *         @OA\Property(property="derniers_partenaires", type="array", @OA\Items(ref="#/components/schemas/Partenaire"))
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="BienPagination",
 *     type="object",
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Bien")),
 *     @OA\Property(property="per_page", type="integer", example=6),
 *     @OA\Property(property="total", type="integer", example=20)
 * )
 * 
 * @OA\Schema(
 *     schema="BienWithRelationsPagination",
 *     type="object",
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/BienWithRelations")),
 *     @OA\Property(property="per_page", type="integer", example=10),
 *     @OA\Property(property="total", type="integer", example=15)
 * )
 * 
 * @OA\Schema(
 *     schema="Bien",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="titre", type="string", example="Appartement spacieux"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="type", type="string", example="Appartement"),
 *     @OA\Property(property="prix", type="number", format="float", example=750.50),
 *     @OA\Property(property="surface", type="integer", example=80),
 *     @OA\Property(property="commune", type="string", example="Paris"),
 *     @OA\Property(property="status", type="string", example="Disponible")
 * )
 * 
 * @OA\Schema(
 *     schema="BienWithRelations",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Bien"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="proprietaire", ref="#/components/schemas/Proprietaire"),
 *             @OA\Property(property="agence", ref="#/components/schemas/Agence")
 *         )
 *     }
 * )
 * 
 * @OA\Schema(
 *     schema="Partenaire",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="nom", type="string", example="Dupont"),
 *     @OA\Property(property="prenom", type="string", example="Jean"),
 *     @OA\Property(property="email", type="string", format="email", example="jean.dupont@example.com"),
 *     @OA\Property(property="type", type="string", example="Propriétaire")
 * )
 * 
 * @OA\Schema(
 *     schema="Proprietaire",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="nom", type="string", example="Dupont"),
 *     @OA\Property(property="prenom", type="string", example="Jean")
 * )
 * 
 * @OA\Schema(
 *     schema="Agence",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="nom", type="string", example="Agence Immo Paris")
 * )
 * 
 * @OA\Schema(
 *     schema="ContactRequest",
 *     type="object",
 *     required={"name", "email", "subject", "message"},
 *     @OA\Property(property="name", type="string", example="Jean Dupont"),
 *     @OA\Property(property="email", type="string", format="email", example="jean.dupont@example.com"),
 *     @OA\Property(property="subject", type="string", example="Demande d'information"),
 *     @OA\Property(property="message", type="string", example="Bonjour, je souhaiterais plus d'informations sur...")
 * )
 * 
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="code_id", type="string", example="LOC12345"),
 *     @OA\Property(property="name", type="string", example="Doe"),
 *     @OA\Property(property="prenom", type="string", example="John"),
 *     @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 *     @OA\Property(property="contact", type="string", example="0102030405"),
 *     @OA\Property(property="status", type="string", example="Actif"),
 *     @OA\Property(property="profile_image", type="string", nullable=true, example="profile_images/image.jpg")
 * )
 * 
 * @OA\Schema(
 *     schema="LocataireWithRelations",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Locataire"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="bien", ref="#/components/schemas/Bien"),
 *             @OA\Property(property="agence", ref="#/components/schemas/Agence")
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="LocataireWithPaiements",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Locataire"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(
 *                 property="paiements",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/Paiement")
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="Paiement",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="reference", type="string", example="PAY-12345"),
 *     @OA\Property(property="montant", type="number", format="float", example=500.00),
 *     @OA\Property(property="date_paiement", type="string", format="date-time", example="2023-10-26T10:00:00Z"),
 *     @OA\Property(property="mois_couvert", type="string", example="2023-10"),
 *     @OA\Property(property="statut", type="string", enum={"payé", "En attente"}, example="payé")
 * )
 *
 * @OA\Schema(
 *     schema="PaiementDetails",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Paiement"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="locataire", type="object", 
 *                 @OA\Property(property="name", type="string"),
 *                 @OA\Property(property="prenom", type="string"),
 *                 @OA\Property(property="email", type="string"),
 *             ),
 *             @OA\Property(property="bien", type="object",
 *                 @OA\Property(property="commune", type="string"),
 *                 @OA\Property(property="type", type="string"),
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="CashVerificationCode",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="code", type="string", example="ABC-123"),
 *     @OA\Property(property="expires_at", type="string", format="date-time", example="2023-10-26T11:00:00Z"),
 *     @OA\Property(property="used_at", type="string", format="date-time", nullable=true, example=null)
 * )
 *
 * @OA\Schema(
 *     schema="Visite",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="nom", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 *     @OA\Property(property="date_visite", type="string", format="date", example="2023-11-15")
 * )
 *
 */
class HomeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api",
     *     summary="Page d'accueil avec statistiques",
     *     description="Retourne les biens disponibles, les compteurs par type et les derniers partenaires",
     *     tags={"Accueil"},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrer par type de bien",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="commune",
     *         in="query",
     *         description="Filtrer par commune",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="prix_max",
     *         in="query",
     *         description="Filtrer par prix maximum",
     *         required=false,
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Succès",
     *         @OA\JsonContent(ref="#/components/schemas/HomeResponse")
     *     )
     * )
     */
    public function index(Request $request)
    {
        // Initialisation de la requête
        $query = Bien::where('status', 'Disponible');
        
        // Filtres de recherche
        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->type);
        }
        
        if ($request->has('commune') && $request->commune != '') {
            $query->where('commune', 'like', '%'.$request->commune.'%');
        }
        
        if ($request->has('prix_max') && $request->prix_max != '') {
            $query->where('prix', '<=', $request->prix_max);
        }
        
        // Pagination et tri
        $biens = $query->orderBy('created_at', 'desc')->paginate(6);
        
        // Compteurs par type (sans les filtres pour garder les totaux)
        $counts = [
            'appartements' => Bien::where('status', 'Disponible')
                                ->where('type', 'Appartement')->count(),
            'maisons' => Bien::where('status', 'Disponible')
                          ->where('type', 'Maison')->count(),
            'terrains' => Bien::where('status', 'Disponible')
                             ->where('type', 'Bureau')->count(),
        ];

        // Récupération des 10 derniers partenaires
        $derniersPartenaires = collect()
            ->merge(
                Proprietaire::orderBy('created_at', 'desc')
                    ->take(10)
                    ->get()
                    ->map(function ($item) {
                        $item->type = 'Propriétaire';
                        return $item;
                    })
            )
            ->merge(
                Agence::orderBy('created_at', 'desc')
                    ->take(10)
                    ->get()
                    ->map(function ($item) {
                        $item->type = 'Agence';
                        return $item;
                    })
            )
            ->sortByDesc('created_at')
            ->take(10);

        return response()->json([
            'success' => true,
            'data' => [
                'biens' => $biens,
                'counts' => $counts,
                'derniers_partenaires' => $derniersPartenaires
            ]
        ]);
    }
/**
 * @OA\Get(
 *     path="/api/biens/available",
 *     summary="Liste des biens disponibles",
 *     description="Retourne une liste paginée des biens disponibles avec filtres optionnels",
 *     tags={"Biens"},
 *     @OA\Parameter(
 *         name="type",
 *         in="query",
 *         description="Filtrer par type de bien",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="commune",
 *         in="query",
 *         description="Filtrer par commune",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="prix_max",
 *         in="query",
 *         description="Filtrer par prix maximum",
 *         required=false,
 *         @OA\Schema(type="number", format="float")
 *     ),
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         description="Nombre d'éléments par page",
 *         required=false,
 *         @OA\Schema(type="integer", default=6)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Succès",
 *         @OA\JsonContent(ref="#/components/schemas/BienPagination")
 *     )
 * )
 */
public function availableApi(Request $request)
{
    // Initialisation de la requête pour les biens disponibles
    $query = Bien::where('status', 'Disponible');
    
    // Filtres de recherche
    if ($request->has('type') && $request->type != '') {
        $query->where('type', $request->type);
    }
    
    if ($request->has('commune') && $request->commune != '') {
        $query->where('commune', 'like', '%'.$request->commune.'%');
    }
    
    if ($request->has('prix_max') && $request->prix_max != '') {
        $query->where('prix', '<=', $request->prix_max);
    }
    
    // Pagination et tri
    $biens = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 6));
    
    // Transformer les données pour regrouper les images dans un tableau
    $transformedBiens = $biens->getCollection()->map(function ($bien) {
        $images = [];
        
        // Ajouter l'image principale
        if (!empty($bien->image)) {
            $images[] = asset('storage/' . $bien->image);
        }
        
        // Ajouter les images supplémentaires
        $additionalImages = [
            $bien->image1,
            $bien->image2,
            $bien->image3,
            $bien->image4,
            $bien->image5
        ];
        
        foreach ($additionalImages as $image) {
            if (!empty($image)) {
                $images[] = asset('storage/' . $image);
            }
        }
        
        // Retourner le bien avec les images transformées
        return [
            'id' => $bien->id,
            'type' => $bien->type,
            'numero_bien' => $bien->numero_bien,
            'utilisation' => $bien->utilisation,
            'description' => $bien->description,
            'superficie' => $bien->superficie,
            'nombre_de_chambres' => $bien->nombre_de_chambres,
            'nombre_de_toilettes' => $bien->nombre_de_toilettes,
            'garage' => $bien->garage,
            'avance' => $bien->avance,
            'caution' => $bien->caution,
            'frais' => $bien->frais,
            'montant_total' => $bien->montant_total,
            'prix' => $bien->prix,
            'commune' => $bien->commune,
            'montant_majore' => $bien->montant_majore,
            'date_fixe' => $bien->date_fixe,
            'images' => $images, // Tableau de toutes les images
            'status' => $bien->status,
            'agence_id' => $bien->agence_id,
            'proprietaire_id' => $bien->proprietaire_id,
            'created_at' => $bien->created_at,
            'updated_at' => $bien->updated_at
        ];
    });
    
    // Remplacer la collection paginée par la collection transformée
    $biens->setCollection($transformedBiens);
    
    return response()->json([
        'success' => true,
        'data' => $biens,
        'message' => 'Liste des biens disponibles récupérée avec succès'
    ]);
}

    /**
     * @OA\Get(
     *     path="/api/biens/appartements",
     *     summary="Liste des appartements disponibles",
     *     description="Retourne une liste paginée des appartements disponibles",
     *     tags={"Biens"},
     *     @OA\Parameter(
     *         name="commune",
     *         in="query",
     *         description="Filtrer par commune",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="prix_max",
     *         in="query",
     *         description="Filtrer par prix maximum",
     *         required=false,
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Succès",
     *         @OA\JsonContent(ref="#/components/schemas/BienPagination")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function appartementsApi(Request $request)
    {
        try {
            // Initialisation de la requête
            $query = Bien::where('status', 'Disponible')
                        ->where('type', 'Appartement');
            
            // Filtres
            if ($request->has('commune')) {
                $query->where('commune', 'like', '%'.$request->commune.'%');
            }
            
            if ($request->has('prix_max')) {
                $query->where('prix', '<=', $request->prix_max);
            }

            // Pagination (optionnel mais recommandé)
            $perPage = $request->get('per_page', 10);
            $biens = $query->paginate($perPage);

            // Formatage de la réponse
            return response()->json([
                'success' => true,
                'data' => $biens,
                'meta' => [
                    'current_page' => $biens->currentPage(),
                    'per_page' => $biens->perPage(),
                    'total' => $biens->total(),
                    'last_page' => $biens->lastPage()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des appartements',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/biens/maisons",
     *     summary="Liste des maisons disponibles",
     *     description="Retourne une liste paginée des maisons disponibles avec les relations propriétaire et agence",
     *     tags={"Biens"},
     *     @OA\Parameter(
     *         name="commune",
     *         in="query",
     *         description="Filtrer par commune",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="prix_max",
     *         in="query",
     *         description="Filtrer par prix maximum",
     *         required=false,
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Succès",
     *         @OA\JsonContent(ref="#/components/schemas/BienWithRelationsPagination")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function maisonsApi(Request $request)
    {
        try {
            $query = Bien::with(['proprietaire', 'agence'])
                        ->where('status', 'Disponible')
                        ->where('type', 'Maison');
            
            if ($request->has('commune')) {
                $query->where('commune', 'like', '%'.$request->commune.'%');
            }
            
            if ($request->has('prix_max')) {
                $query->where('prix', '<=', $request->prix_max);
            }

            // Pagination
            $perPage = $request->get('per_page', 10);
            $biens = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $biens,
                'meta' => [
                    'current_page' => $biens->currentPage(),
                    'per_page' => $biens->perPage(),
                    'total' => $biens->total(),
                    'last_page' => $biens->lastPage()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des maisons',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/biens/bureaux",
     *     summary="Liste des terrains disponibles",
     *     description="Retourne une liste paginée des terrains disponibles",
     *     tags={"Biens"},
     *     @OA\Parameter(
     *         name="commune",
     *         in="query",
     *         description="Filtrer par commune",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="prix_max",
     *         in="query",
     *         description="Filtrer par prix maximum",
     *         required=false,
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Bien")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=15),
     *                 @OA\Property(property="last_page", type="integer", example=2)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function terrainsApi(Request $request)
    {
        try {
            $query = Bien::where('status', 'Disponible')
                        ->where('type', 'Bureau'); // Note: Vous aviez 'Bureau' dans votre méthode originale
            
            if ($request->has('commune')) {
                $query->where('commune', 'like', '%'.$request->commune.'%');
            }
            
            if ($request->has('prix_max')) {
                $query->where('prix', '<=', $request->prix_max);
            }

            // Pagination
            $perPage = $request->get('per_page', 10);
            $biens = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $biens->items(),
                'meta' => [
                    'current_page' => $biens->currentPage(),
                    'per_page' => $biens->perPage(),
                    'total' => $biens->total(),
                    'last_page' => $biens->lastPage()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des terrains',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/contact",
     *     summary="Envoyer un message de contact",
     *     description="Envoie un email de contact à l'administrateur",
     *     tags={"Contact"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ContactRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message envoyé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Votre message a été envoyé avec succès!"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="email", type="string", format="email"),
     *                 @OA\Property(property="subject", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur de validation"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(type="string", example="Le champ email est obligatoire.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function send(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
            ]);

            // Envoi de l'email
            Mail::to('contact@maelysimo.com')->send(new ContactMail($validated));

            return response()->json([
                'success' => true,
                'message' => 'Votre message a été envoyé avec succès!',
                'data' => [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'subject' => $validated['subject']
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->validator->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/biens/{id}",
     *     summary="Détails d'un bien",
     *     description="Retourne les détails complets d'un bien spécifique",
     *     tags={"Biens"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du bien",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/BienWithRelations")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Bien non trouvé")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            // Recherche le bien par son ID
            $bien = Bien::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $bien
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bien non trouvé'
            ], 404);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/biens/types/avec-ids",
     *     summary="Liste des types de biens disponibles avec leurs IDs",
     *     description="Retourne la liste de tous les types de biens distincts avec leurs IDs correspondants",
     *     tags={"Biens"},
     *     @OA\Response(
     *         response=200,
     *         description="Succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="type", type="string", example="Appartement")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function typesDeBiensApi()
    {
        try {
            // Récupère les IDs et les noms des types distincts
            $types = Bien::where('status', 'Disponible')
                        ->select('id', 'type')
                        ->distinct('type')
                        ->orderBy('type')
                        ->get()
                        ->map(function ($bien) {
                            return [
                                'id' => $bien->id,
                                'type' => $bien->type
                            ];
                        });

            return response()->json([
                'success' => true,
                'data' => $types
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la liste des types avec IDs',
                'error' => $e->getMessage()
            ], 500);
        }
}
}