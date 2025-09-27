<?php

namespace App\Http\Controllers\Api\Locataire;

use App\Http\Controllers\Controller;
use App\Models\EtatLieu;
use App\Models\EtatLieuSorti;
use App\Models\VerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EtatLieuController extends Controller
{
    /**
     * Récupérer la liste des états des lieux à l'entrée du locataire connecté
     * 
     * @OA\Get(
     *     path="/api/etat-lieu/entree",
     *     summary="Liste des états des lieux à l'entrée",
     *     description="Retourne la liste des états des lieux à l'entrée du locataire connecté",
     *     tags={"Etat des Lieux"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="etats_lieu", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié"
     *     )
     * )
     */
    public function getEtatsLieuEntree()
    {
        try {
            // Récupérer le locataire connecté
            $locataire = Auth::user();
            
            if (!$locataire) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            // Récupérer tous les états des lieux à l'entrée du locataire
            $etatsLieu = EtatLieu::where('locataire_id', $locataire->id)
                ->with(['bien']) // Charger les relations nécessaires
                ->orderBy('created_at', 'desc')
                ->get();

            // Décoder les champs JSON pour chaque état des lieux
            $etatsLieu->transform(function ($etatLieu) {
                return $this->decodeJsonFields($etatLieu);
            });

            return response()->json([
                'success' => true,
                'message' => 'Liste des états des lieux à l\'entrée récupérée avec succès',
                'data' => [
                    'etats_lieu' => $etatsLieu,
                    'count' => $etatsLieu->count()
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
     * Récupérer la liste des états des lieux à la sortie du locataire connecté
     * 
     * @OA\Get(
     *     path="/api/etat-lieu/sortie",
     *     summary="Liste des états des lieux à la sortie",
     *     description="Retourne la liste des états des lieux à la sortie du locataire connecté",
     *     tags={"Etat des Lieux"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Succès"
     *     )
     * )
     */
    public function getEtatsLieuSortie()
    {
        try {
            $locataire = Auth::user();
            
            if (!$locataire) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            // Récupérer tous les états des lieux à la sortie du locataire
            $etatsLieuSortie = EtatLieuSorti::where('locataire_id', $locataire->id)
                ->with(['bien'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Décoder les champs JSON
            $etatsLieuSortie->transform(function ($etatLieu) {
                return $this->decodeJsonFields($etatLieu);
            });

            return response()->json([
                'success' => true,
                'message' => 'Liste des états des lieux à la sortie récupérée avec succès',
                'data' => [
                    'etats_lieu_sortie' => $etatsLieuSortie,
                    'count' => $etatsLieuSortie->count()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des états des lieux à la sortie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
 * Récupérer les états des lieux (entrée et sortie) du locataire connecté
 * 
 * @OA\Get(
 *     path="/api/tenant/all",
 *     summary="États des lieux du locataire",
 *     description="Retourne les états des lieux d'entrée et de sortie du locataire connecté avec les données décodées",
 *     tags={"Etat des Lieux"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="locataire", type="object",
 *                     @OA\Property(property="id", type="integer"),
 *                     @OA\Property(property="name", type="string"),
 *                     @OA\Property(property="prenom", type="string"),
 *                     @OA\Property(property="email", type="string"),
 *                     @OA\Property(property="contact", type="string"),
 *                     @OA\Property(property="bien_id", type="integer")
 *                 ),
 *                 @OA\Property(property="comptable", type="object",
 *                     @OA\Property(property="id", type="integer"),
 *                     @OA\Property(property="name", type="string"),
 *                     @OA\Property(property="email", type="string"),
 *                     @OA\Property(property="contact", type="string")
 *                 ),
 *                 @OA\Property(property="etat_lieu_entree", type="object", nullable=true,
 *                     @OA\Property(property="id", type="integer"),
 *                     @OA\Property(property="type_etat", type="string"),
 *                     @OA\Property(property="date_etat", type="string", format="date-time"),
 *                     @OA\Property(property="status_etat_entre", type="string"),
 *                     @OA\Property(property="status_etat_sortie", type="string"),
 *                     @OA\Property(property="remarques", type="string", nullable=true),
 *                     @OA\Property(property="parties_communes", type="object", nullable=true),
 *                     @OA\Property(property="chambres", type="object", nullable=true),
 *                     @OA\Property(property="created_at", type="string", format="date-time"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time")
 *                 ),
 *                 @OA\Property(property="etat_lieu_sortie", type="object", nullable=true,
 *                     @OA\Property(property="id", type="integer"),
 *                     @OA\Property(property="type_etat", type="string"),
 *                     @OA\Property(property="date_etat", type="string", format="date-time"),
 *                     @OA\Property(property="status_etat_entre", type="string"),
 *                     @OA\Property(property="status_etat_sortie", type="string"),
 *                     @OA\Property(property="remarques", type="string", nullable=true),
 *                     @OA\Property(property="parties_communes", type="object", nullable=true),
 *                     @OA\Property(property="chambres", type="object", nullable=true),
 *                     @OA\Property(property="created_at", type="string", format="date-time"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time")
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
public function getAllEtatsLieu()
{
    try {
        // Récupérer le locataire connecté
        $locataire = Auth::user();
        
        if (!$locataire) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }
        
        // Récupérer le comptable associé au locataire
        $comptable = $locataire->comptable;
        
        // Récupérer l'état des lieux d'entrée s'il existe
        $etatLieu = EtatLieu::where('locataire_id', $locataire->id)
            ->where('bien_id', $locataire->bien_id)
            ->first();
        
        // Décoder les champs JSON pour l'état des lieux d'entrée
        if ($etatLieu) {
            if ($etatLieu->parties_communes) {
                $etatLieu->parties_communes = json_decode($etatLieu->parties_communes, true);
            }
            if ($etatLieu->chambres) {
                $etatLieu->chambres = json_decode($etatLieu->chambres, true);
            }
            if ($etatLieu->etat_des_lieux) {
                $etatLieu->etat_des_lieux = json_decode($etatLieu->etat_des_lieux, true);
            }
            if ($etatLieu->constat) {
                $etatLieu->constat = json_decode($etatLieu->constat, true);
            }
            if ($etatLieu->diagnostic) {
                $etatLieu->diagnostic = json_decode($etatLieu->diagnostic, true);
            }
            if ($etatLieu->elements_bien) {
                $etatLieu->elements_bien = json_decode($etatLieu->elements_bien, true);
            }
        }

        // Récupérer l'état des lieux de sortie s'il existe
        $etatLieuSortie = EtatLieuSorti::where('locataire_id', $locataire->id)
            ->where('bien_id', $locataire->bien_id)
            ->first();
        
        // Décoder les champs JSON pour l'état des lieux de sortie
        if ($etatLieuSortie) {
            if ($etatLieuSortie->parties_communes) {
                $etatLieuSortie->parties_communes = json_decode($etatLieuSortie->parties_communes, true);
            }
            if ($etatLieuSortie->chambres) {
                $etatLieuSortie->chambres = json_decode($etatLieuSortie->chambres, true);
            }
            if ($etatLieuSortie->etat_des_lieux) {
                $etatLieuSortie->etat_des_lieux = json_decode($etatLieuSortie->etat_des_lieux, true);
            }
            if ($etatLieuSortie->constat) {
                $etatLieuSortie->constat = json_decode($etatLieuSortie->constat, true);
            }
            if ($etatLieuSortie->diagnostic) {
                $etatLieuSortie->diagnostic = json_decode($etatLieuSortie->diagnostic, true);
            }
            if ($etatLieuSortie->elements_bien) {
                $etatLieuSortie->elements_bien = json_decode($etatLieuSortie->elements_bien, true);
            }
        }

        // Préparer la réponse
        $response = [
            'success' => true,
            'message' => 'États des lieux récupérés avec succès',
            'data' => [
                'locataire' => [
                    'id' => $locataire->id,
                    'name' => $locataire->name,
                    'prenom' => $locataire->prenom,
                    'email' => $locataire->email,
                    'contact' => $locataire->contact,
                    'bien_id' => $locataire->bien_id
                ],
                'comptable' => $comptable ? [
                    'id' => $comptable->id,
                    'name' => $comptable->name,
                    'email' => $comptable->email,
                    'contact' => $comptable->contact
                ] : null,
                'etat_lieu_entree' => $etatLieu,
                'etat_lieu_sortie' => $etatLieuSortie
            ]
        ];

        return response()->json($response, 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération des états des lieux',
            'error' => $e->getMessage()
        ], 500);
    }
}
    /**
     * Récupérer un état des lieux spécifique à l'entrée
     * 
     * @OA\Get(
     *     path="/api/etat-lieu/entree/{id}",
     *     summary="Détails d'un état des lieux à l'entrée",
     *     description="Retourne les détails d'un état des lieux à l'entrée spécifique",
     *     tags={"Etat des Lieux"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de l'état des lieux",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Succès"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="État des lieux non trouvé"
     *     )
     * )
     */
    public function getEtatLieuEntreeById($id)
    {
        try {
            $locataire = Auth::user();
            
            if (!$locataire) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            // Récupérer l'état des lieux spécifique du locataire connecté
            $etatLieu = EtatLieu::where('id', $id)
                ->where('locataire_id', $locataire->id)
                ->with(['bien', 'locataire'])
                ->first();

            if (!$etatLieu) {
                return response()->json([
                    'success' => false,
                    'message' => 'État des lieux non trouvé ou accès non autorisé'
                ], 404);
            }

            // Décoder les champs JSON
            $etatLieu = $this->decodeJsonFields($etatLieu);

            return response()->json([
                'success' => true,
                'message' => 'État des lieux récupéré avec succès',
                'data' => [
                    'etat_lieu' => $etatLieu
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'état des lieux',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Décoder les champs JSON de l'état des lieux
     */
    private function decodeJsonFields($etatLieu)
    {
        if ($etatLieu->parties_communes) {
            $etatLieu->parties_communes = json_decode($etatLieu->parties_communes, true);
        }
        
        if ($etatLieu->chambres) {
            $etatLieu->chambres = json_decode($etatLieu->chambres, true);
        }
        
        if ($etatLieu->autres_pieces) {
            $etatLieu->autres_pieces = json_decode($etatLieu->autres_pieces, true);
        }
        
        if ($etatLieu->observations) {
            $etatLieu->observations = json_decode($etatLieu->observations, true);
        }

        return $etatLieu;
    }
}