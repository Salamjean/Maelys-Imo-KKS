<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use App\Models\EtatLieu;
use App\Models\Locataire;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Str;

class ApiAgentEtatLieu extends Controller
{
    /**
     * @OA\Get(
     *     path="/agent/etats-lieu/warning",
     *     summary="Récupérer les locataires avec états des lieux en attente",
     *     description="Retourne la liste des locataires qui n'ont pas d'état des lieux d'entrée avec statut 'Oui'",
     *     tags={"Agent - États des Lieux"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="locataires",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="nom", type="string"),
     *                         @OA\Property(property="prenom", type="string"),
     *                         @OA\Property(property="email", type="string"),
     *                         @OA\Property(property="contact", type="string"),
     *                         @OA\Property(property="type_bien", type="string", nullable=true),
     *                         @OA\Property(property="commune_bien", type="string", nullable=true),
     *                         @OA\Property(property="date_etat_lieu", type="string"),
     *                         @OA\Property(property="status_etat_entre", type="string")
     *                     )
     *                 ),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
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
    public function getLocataireAvecEtatsLieuEnAttente()
    {
        try {
            $comptable = Auth::guard('sanctum')->user();
            
            if (!$comptable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            // Récupérer les locataires qui ont ce comptable assigné et qui n'ont pas d'état des lieux d'entrée avec statut "Oui"
            $locataires = Locataire::with(['bien', 'etatLieu' => function($query) {
                    $query->orderBy('created_at', 'desc');
                }])
                ->where('comptable_id', $comptable->id)
                ->where('status', 'Actif')
                ->whereDoesntHave('etatLieu', function($query) {
                    $query->where('status_etat_entre', 'Oui');
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($locataire) {
                    // Récupérer le dernier état des lieux (même s'il n'a pas le statut "Oui")
                    $dernierEtatLieu = $locataire->etatLieu->first();
                    
                    return [
                        'id' => $locataire->id,
                        'nom' => $locataire->name,
                        'prenom' => $locataire->prenom,
                        'email' => $locataire->email,
                        'contact' => $locataire->contact,
                        'type_bien' => $locataire->bien ? $locataire->bien->type : null,
                        'commune_bien' => $locataire->bien ? $locataire->bien->commune : null,
                        'date_etat_lieu' => $dernierEtatLieu ? 
                            $dernierEtatLieu->created_at->format('d/m/Y H:i') : $locataire->created_at->format('d/m/Y H:i'),
                        'status_etat_entre' => $dernierEtatLieu ? $dernierEtatLieu->status_etat_entre : 'en attente'
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'locataires' => $locataires,
                    'total' => $locataires->count()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des locataires',
                'error' => $e->getMessage()
            ], 500);
        }
    }

/**
 * @OA\Get(
 *     path="/agent/etats-lieu/{locataireId}/details",
 *     summary="Récupérer les données complètes d'un locataire avec son bien et états des lieux",
 *     description="Retourne les données d'un locataire avec les informations de son bien et tous ses états des lieux",
 *     tags={"Agent - Locataires"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="locataireId",
 *         in="path",
 *         required=true,
 *         description="ID du locataire",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="locataire", type="object",
 *                     @OA\Property(property="id", type="integer"),
 *                     @OA\Property(property="nom", type="string"),
 *                     @OA\Property(property="prenom", type="string"),
 *                     @OA\Property(property="email", type="string"),
 *                     @OA\Property(property="contact", type="string"),
 *                     @OA\Property(property="status", type="string"),
 *                     @OA\Property(property="date_entree", type="string", format="date"),
 *                     @OA\Property(property="date_sortie", type="string", format="date", nullable=true),
 *                     @OA\Property(property="comptable_id", type="integer")
 *                 ),
 *                 @OA\Property(property="bien", type="object",
 *                     @OA\Property(property="id", type="integer"),
 *                     @OA\Property(property="type", type="string"),
 *                     @OA\Property(property="adresse", type="string"),
 *                     @OA\Property(property="commune", type="string"),
 *                     @OA\Property(property="ville", type="string"),
 *                     @OA\Property(property="code_postal", type="string"),
 *                     @OA\Property(property="surface", type="number", format="float"),
 *                     @OA\Property(property="loyer", type="number", format="float")
 *                 ),
 *                 @OA\Property(property="etats_lieu", type="object",
 *                     @OA\Property(property="etat_entree", type="object", nullable=true,
 *                         @OA\Property(property="id", type="integer"),
 *                         @OA\Property(property="type_bien", type="string"),
 *                         @OA\Property(property="commune_bien", type="string"),
 *                         @OA\Property(property="presence_partie", type="string"),
 *                         @OA\Property(property="status_etat_entre", type="string"),
 *                         @OA\Property(property="status_sorti", type="string"),
 *                         @OA\Property(property="parties_communes", type="object"),
 *                         @OA\Property(property="chambres", type="object"),
 *                         @OA\Property(property="nombre_cle", type="string"),
 *                         @OA\Property(property="created_at", type="string", format="date-time"),
 *                         @OA\Property(property="updated_at", type="string", format="date-time")
 *                     ),
 *                     @OA\Property(property="etat_sortie", type="object", nullable=true,
 *                         @OA\Property(property="id", type="integer"),
 *                         @OA\Property(property="type_bien", type="string"),
 *                         @OA\Property(property="commune_bien", type="string"),
 *                         @OA\Property(property="presence_partie", type="string"),
 *                         @OA\Property(property="status_etat_entre", type="string"),
 *                         @OA\Property(property="status_sorti", type="string"),
 *                         @OA\Property(property="parties_communes", type="object"),
 *                         @OA\Property(property="chambres", type="object"),
 *                         @OA\Property(property="nombre_cle", type="string"),
 *                         @OA\Property(property="created_at", type="string", format="date-time"),
 *                         @OA\Property(property="updated_at", type="string", format="date-time")
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Non authentifié",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Locataire non trouvé",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string")
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
public function getLocataireAvecBienEtEtatsLieu($locataireId)
{
    try {
        $comptable = Auth::guard('sanctum')->user();
        
        if (!$comptable) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        // Récupérer le locataire avec ses relations
        $locataire = Locataire::with(['bien', 'etatLieu' => function($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->where('id', $locataireId)
            ->where('comptable_id', $comptable->id)
            ->first();

        if (!$locataire) {
            return response()->json([
                'success' => false,
                'message' => 'Locataire non trouvé'
            ], 404);
        }

        // Structurer les données de réponse
        $data = [
            'locataire' => [
                'id' => $locataire->id,
                'nom' => $locataire->name,
                'prenom' => $locataire->prenom,
                'email' => $locataire->email,
                'contact' => $locataire->contact,
                'status' => $locataire->status,
                'date_entree' => $locataire->date_entree ? $locataire->date_entree->format('Y-m-d') : null,
                'date_sortie' => $locataire->date_sortie ? $locataire->date_sortie->format('Y-m-d') : null,
                'comptable_id' => $locataire->comptable_id
            ],
            'bien' => null,
            'etats_lieu' => [
                'etat_entree' => null,
                'etat_sortie' => null
            ]
        ];

        // Ajouter les informations du bien si exists
        if ($locataire->bien) {
            $data['bien'] = [
                'id' => $locataire->bien->id,
                'type' => $locataire->bien->type,
                'adresse' => $locataire->bien->adresse,
                'commune' => $locataire->bien->commune,
                'ville' => $locataire->bien->ville,
                'code_postal' => $locataire->bien->code_postal,
                'surface' => $locataire->bien->surface,
                'loyer' => $locataire->bien->loyer
            ];
        }

        // Séparer les états des lieux par type
        if ($locataire->etatLieu->isNotEmpty()) {
            foreach ($locataire->etatLieu as $etatLieu) {
                $etatData = [
                    'id' => $etatLieu->id,
                    'type_bien' => $etatLieu->type_bien,
                    'commune_bien' => $etatLieu->commune_bien,
                    'presence_partie' => $etatLieu->presence_partie,
                    'status_etat_entre' => $etatLieu->status_etat_entre,
                    'status_sorti' => $etatLieu->status_sorti,
                    'parties_communes' => $etatLieu->parties_communes ? json_decode($etatLieu->parties_communes, true) : null,
                    'chambres' => $etatLieu->chambres ? json_decode($etatLieu->chambres, true) : null,
                    'nombre_cle' => $etatLieu->nombre_cle,
                    'created_at' => $etatLieu->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $etatLieu->updated_at->format('Y-m-d H:i:s')
                ];

                // Déterminer le type d'état basé sur le status
                if ($etatLieu->status_etat_entre === 'Oui' || $etatLieu->status_etat_entre === 'En attente') {
                    $data['etats_lieu']['etat_entree'] = $etatData;
                } elseif ($etatLieu->status_sorti === 'Oui' || $etatLieu->status_sorti === 'En attente') {
                    $data['etats_lieu']['etat_sortie'] = $etatData;
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération des données du locataire',
            'error' => $e->getMessage()
        ], 500);
    }
}


    /**
     * @OA\Get(
     *     path="/agent/etats/end",
     *     summary="Récupérer les états des lieux terminés",
     *     description="Retourne la liste des états des lieux avec statut 'Oui' (terminés)",
     *     tags={"Agent - États des Lieux"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="etats_lieu",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="type_bien", type="string"),
     *                         @OA\Property(property="commune_bien", type="string"),
     *                         @OA\Property(property="nom_locataire", type="string"),
     *                         @OA\Property(property="prenom_locataire", type="string"),
     *                         @OA\Property(property="date_creation", type="string"),
     *                         @OA\Property(property="status_etat_entre", type="string"),
     *                         @OA\Property(property="bien_numero", type="string"),
     *                         @OA\Property(property="bien_adresse", type="string")
     *                     )
     *                 ),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
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
    public function getEtatsLieuFin(Request $request)
    {
        try {
            $comptable = Auth::guard('sanctum')->user();
            
            if (!$comptable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            // Récupérer les états des lieux avec statut "en attente"
            $etatsLieu = EtatLieu::with(['locataire', 'bien'])
                ->where('status_etat_entre', 'Oui')
                ->whereHas('locataire', function($query) use ($comptable) {
                    $query->where(function($subQuery) use ($comptable) {
                        if ($comptable->agence_id) {
                            $subQuery->orWhere('agence_id', $comptable->agence_id);
                        }
                        if ($comptable->proprietaire_id) {
                            $subQuery->orWhere('proprietaire_id', $comptable->proprietaire_id);
                        }
                        $subQuery->orWhere('comptable_id', $comptable->id);
                    });
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($etatLieu) {
                    return [
                        'id' => $etatLieu->id,
                        'type_bien' => $etatLieu->type_bien,
                        'commune_bien' => $etatLieu->commune_bien,
                        'nom_locataire' => $etatLieu->locataire->name,
                        'prenom_locataire' => $etatLieu->locataire->prenom,
                        'date_creation' => $etatLieu->created_at->format('d/m/Y H:i'),
                        'status_etat_entre' => $etatLieu->status_etat_entre,
                        'bien_numero' => $etatLieu->bien->numero_bien,
                        'bien_adresse' => $etatLieu->bien->adresse
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'etats_lieu' => $etatsLieu,
                    'total' => $etatsLieu->count()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des états des lieux en attente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/agent/etats-lieu/effectues",
     *     summary="Récupérer tous les états des lieux effectués",
     *     description="Retourne tous les états des lieux avec pagination et détails complets",
     *     tags={"Agent - États des Lieux"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Nombre d'éléments par page (défaut: 10)",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page (défaut: 1)",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="etats_lieu",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(
     *                             property="informations_generales",
     *                             type="object",
     *                             @OA\Property(property="type_bien", type="string"),
     *                             @OA\Property(property="commune_bien", type="string"),
     *                             @OA\Property(property="presence_partie", type="string"),
     *                             @OA\Property(property="status_etat_entre", type="string"),
     *                             @OA\Property(property="status_sorti", type="string"),
     *                             @OA\Property(property="nombre_cle", type="integer"),
     *                             @OA\Property(property="date_creation", type="string"),
     *                             @OA\Property(property="date_modification", type="string")
     *                         ),
     *                         @OA\Property(property="parties_communes", type="object"),
     *                         @OA\Property(property="chambres", type="array", @OA\Items(type="object")),
     *                         @OA\Property(
     *                             property="locataire",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer"),
     *                             @OA\Property(property="code_id", type="string"),
     *                             @OA\Property(property="nom", type="string"),
     *                             @OA\Property(property="prenom", type="string"),
     *                             @OA\Property(property="email", type="string"),
     *                             @OA\Property(property="contact", type="string"),
     *                             @OA\Property(property="adresse", type="string"),
     *                             @OA\Property(property="profession", type="string"),
     *                             @OA\Property(property="piece_identite", type="string"),
     *                             @OA\Property(property="status", type="string")
     *                         ),
     *                         @OA\Property(
     *                             property="bien",
     *                             type="object",
     *                             nullable=true
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="pagination",
     *                     type="object",
     *                     @OA\Property(property="current_page", type="integer"),
     *                     @OA\Property(property="per_page", type="integer"),
     *                     @OA\Property(property="total", type="integer"),
     *                     @OA\Property(property="last_page", type="integer"),
     *                     @OA\Property(property="from", type="integer"),
     *                     @OA\Property(property="to", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
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
    public function getAllEtatsLieuEffectues(Request $request)
    {
        try {
            $comptable = Auth::guard('sanctum')->user();
            
            if (!$comptable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);

            // Récupérer tous les états des lieux avec toutes les informations
            $etatsLieu = EtatLieu::with(['locataire', 'bien'])
                ->whereHas('locataire', function($query) use ($comptable) {
                    $query->where('comptable_id', $comptable->id)
                        ->where('status', 'Actif');
                })
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Transformer les données
            $etatsLieuData = $etatsLieu->getCollection()->map(function($etatLieu) {
                // Décoder les champs JSON
                $partiesCommunes = $etatLieu->parties_communes ? json_decode($etatLieu->parties_communes, true) : [];
                $chambres = $etatLieu->chambres ? json_decode($etatLieu->chambres, true) : [];

                return [
                    'id' => $etatLieu->id,
                    'informations_generales' => [
                        'type_bien' => $etatLieu->type_bien,
                        'commune_bien' => $etatLieu->commune_bien,
                        'presence_partie' => $etatLieu->presence_partie,
                        'status_etat_entre' => $etatLieu->status_etat_entre,
                        'status_sorti' => $etatLieu->status_sorti,
                        'nombre_cle' => $etatLieu->nombre_cle,
                        'date_creation' => $etatLieu->created_at->format('d/m/Y H:i'),
                        'date_modification' => $etatLieu->updated_at->format('d/m/Y H:i')
                    ],
                    
                    'parties_communes' => $partiesCommunes,
                    
                    'chambres' => $chambres,
                    
                    'locataire' => [
                        'id' => $etatLieu->locataire->id,
                        'code_id' => $etatLieu->locataire->code_id,
                        'nom' => $etatLieu->locataire->name,
                        'prenom' => $etatLieu->locataire->prenom,
                        'email' => $etatLieu->locataire->email,
                        'contact' => $etatLieu->locataire->contact,
                        'adresse' => $etatLieu->locataire->adresse,
                        'profession' => $etatLieu->locataire->profession,
                        'piece_identite' => $etatLieu->locataire->piece,
                        'status' => $etatLieu->locataire->status
                    ],
                    
                    'bien' => $etatLieu->bien ? [
                        'id' => $etatLieu->bien->id,
                        'numero_bien' => $etatLieu->bien->numero_bien,
                        'type' => $etatLieu->bien->type,
                        'utilisation' => $etatLieu->bien->utilisation,
                        'description' => $etatLieu->bien->description,
                        'superficie' => $etatLieu->bien->superficie,
                        'nombre_de_chambres' => $etatLieu->bien->nombre_de_chambres,
                        'nombre_de_toilettes' => $etatLieu->bien->nombre_de_toilettes,
                        'garage' => $etatLieu->bien->garage,
                        'prix' => $etatLieu->bien->prix,
                        'commune' => $etatLieu->bien->commune,
                        'adresse' => $etatLieu->bien->adresse,
                        'avance' => $etatLieu->bien->avance,
                        'caution' => $etatLieu->bien->caution,
                        'frais' => $etatLieu->bien->frais,
                        'montant_total' => $etatLieu->bien->montant_total,
                        'status' => $etatLieu->bien->status
                    ] : null
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'etats_lieu' => $etatsLieuData,
                    'pagination' => [
                        'current_page' => $etatsLieu->currentPage(),
                        'per_page' => $etatsLieu->perPage(),
                        'total' => $etatsLieu->total(),
                        'last_page' => $etatsLieu->lastPage(),
                        'from' => $etatsLieu->firstItem(),
                        'to' => $etatsLieu->lastItem()
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des états des lieux',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/agent/etat-lieux",
     *     summary="Créer un nouvel état des lieux",
     *     description="Enregistre un nouvel état des lieux pour un locataire et un bien",
     *     tags={"Agent - États des Lieux"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"locataire_id", "bien_id", "presence_partie", "chambres", "nombre_cle"},
     *             @OA\Property(property="locataire_id", type="integer", example=1),
     *             @OA\Property(property="bien_id", type="integer", example=1),
     *             @OA\Property(property="type_bien", type="string", example="Appartement", nullable=true),
     *             @OA\Property(property="commune_bien", type="string", example="Paris", nullable=true),
     *             @OA\Property(property="presence_partie", type="string", example="oui", enum={"oui", "non"}),
     *             @OA\Property(property="status_etat_entre", type="string", example="En attente", nullable=true),
     *             @OA\Property(
     *                 property="parties_communes",
     *                 type="object",
     *                 @OA\Property(property="sol", type="string", example="Bon état"),
     *                 @OA\Property(property="observation_sol", type="string", example="Quelques rayures"),
     *                 @OA\Property(property="murs", type="string", example="Très bon état"),
     *                 @OA\Property(property="observation_murs", type="string", example="Peinture fraîche"),
     *                 @OA\Property(property="plafond", type="string", example="Bon état"),
     *                 @OA\Property(property="observation_plafond", type="string", example="Propre"),
     *                 @OA\Property(property="porte_entre", type="string", example="Fonctionnelle"),
     *                 @OA\Property(property="observation_porte_entre", type="string", example="Serrure à changer"),
     *                 @OA\Property(property="interrupteur", type="string", example="Fonctionnel"),
     *                 @OA\Property(property="observation_interrupteur", type="string", example="Tous opérationnels"),
     *                 @OA\Property(property="robinet", type="string", example="Fonctionnel"),
     *                 @OA\Property(property="observation_robinet", type="string", example="Pas de fuite"),
     *                 @OA\Property(property="lavabo", type="string", example="Bon état"),
     *                 @OA\Property(property="observation_lavabo", type="string", example="Propre"),
     *                 @OA\Property(property="douche", type="string", example="Fonctionnelle"),
     *                 @OA\Property(property="observation_douche", type="string", example="Bon débit d'eau")
     *             ),
     *             @OA\Property(
     *                 property="chambres",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"nom"},
     *                     @OA\Property(property="nom", type="string", example="Chambre principale"),
     *                     @OA\Property(property="sol", type="string", example="Parquet"),
     *                     @OA\Property(property="observation_sol", type="string", example="Quelques rayures"),
     *                     @OA\Property(property="murs", type="string", example="Peints"),
     *                     @OA\Property(property="observation_murs", type="string", example="Propres"),
     *                     @OA\Property(property="plafond", type="string", example="Blanc"),
     *                     @OA\Property(property="observation_plafond", type="string", example="Sans tache")
     *                 )
     *             ),
     *             @OA\Property(property="nombre_cle", type="integer", example=2, minimum=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="État des lieux créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="État des lieux enregistré avec succès."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Données invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
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
    public function store(Request $request): JsonResponse
    {
        // 1. Validation
        $validated = $request->validate([
            'locataire_id' => 'required|exists:locataires,id',
            'bien_id' => 'required|exists:biens,id',
            'type_bien' => 'nullable|string',
            'commune_bien' => 'nullable|string', // On valide commune_bien, pas adresse_bien
            'presence_partie' => 'required|string',
            'nombre_cle' => 'required|integer',
            
            // Validation du contenu JSON
            'parties_communes' => 'nullable|array',
            'chambres' => 'nullable|array',
        ]);

        try {
            // Récupération du locataire avec son bien
            $locataire = Locataire::with(['bien'])->findOrFail($validated['locataire_id']);

            $etatLieu = new EtatLieu();

            // --- 1. Clés Étrangères ---
            $etatLieu->locataire_id = $locataire->id;
            $etatLieu->bien_id = $validated['bien_id']; // Ou $locataire->bien_id si c'est lié
            
            // Si ta table a une colonne agence_id, décommente la ligne suivante :
            // $etatLieu->agence_id = $locataire->agence_id; 

            // --- 2. Informations Générales (Correspondance exacte BDD) ---
            
            // Type de bien
            $etatLieu->type_bien = $request->type_bien ?? ($locataire->bien ? $locataire->bien->type : null);
            
            // Commune (Remplace adresse_bien qui n'existe pas)
            $etatLieu->commune_bien = $request->commune_bien ?? ($locataire->bien ? $locataire->bien->commune : null);
            
            $etatLieu->presence_partie = $request->presence_partie;
            $etatLieu->nombre_cle = $request->nombre_cle;

            // --- 3. Statuts par défaut ---
            $etatLieu->status_etat_entre = 'En attente'; // Valeur par défaut
            $etatLieu->status_sorti = 'Non';            // Valeur par défaut

            // --- 4. Gestion des colonnes JSON ---

            // Parties Communes : On encode le tableau reçu en JSON
            // Cela va contenir sol, murs, plafond, remarques, etc. tout ce qui est envoyé dans l'objet "parties_communes"
            $partiesCommunesData = $request->input('parties_communes', []);
            $etatLieu->parties_communes = json_encode($partiesCommunesData);

            // Chambres : On encode le tableau reçu en JSON
            if ($request->has('chambres')) {
                $etatLieu->chambres = json_encode($request->chambres);
            } else {
                $etatLieu->chambres = json_encode([]); // Tableau vide par défaut si null
            }

            // Enregistrement
            $etatLieu->save();

            // Préparation de la réponse (on décode le JSON pour l'affichage)
            $response = $etatLieu->toArray();
            $response['parties_communes'] = json_decode($etatLieu->parties_communes);
            $response['chambres'] = json_decode($etatLieu->chambres);

            return response()->json([
                'success' => true,
                'message' => 'État des lieux enregistré avec succès.',
                'data' => $response
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Erreur lors de l'enregistrement",
                'error' => $e->getMessage() // Utile pour le debug, à masquer en prod si besoin
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/etat-lieux/{id}/download",
     *     summary="Télécharger un état des lieux en PDF",
     *     description="Génère et télécharge un état des lieux au format PDF",
     *     tags={"Agent - États des Lieux"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de l'état des lieux",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="PDF généré avec succès",
     *         @OA\MediaType(
     *             mediaType="application/pdf",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="État des lieux non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function downloadApi($id)
    {
        try {
            $etatLieu = EtatLieu::with(['locataire', 'bien'])->findOrFail($id);
            
            // Décoder les JSON
            $etatLieu->parties_communes = json_decode($etatLieu->parties_communes, true);
            $etatLieu->chambres = json_decode($etatLieu->chambres, true);
            
            $pdf = PDF::loadView('agent.etat_lieu.pdf', compact('etatLieu'));
            
            $filename = 'etat-lieux-'.$etatLieu->locataire->name.'-'.$etatLieu->created_at->format('d-m-Y').'.pdf';
            
            // Utilisez la même approche que votre fonction originale
            return $pdf->download($filename);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'État des lieux non trouvé',
                'message' => 'Aucun état des lieux correspondant à cet ID'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur serveur',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}