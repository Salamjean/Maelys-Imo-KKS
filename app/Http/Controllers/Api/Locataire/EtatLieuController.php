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
     * Récupérer tous les états des lieux (entrée et sortie) du locataire connecté
     * 
     * @OA\Get(
     *     path="/api/etat-lieu/all",
     *     summary="Tous les états des lieux",
     *     description="Retourne tous les états des lieux (entrée et sortie) du locataire connecté",
     *     tags={"Etat des Lieux"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Succès"
     *     )
     * )
     */
    public function getAllEtatsLieu()
    {
        try {
            $locataire = Auth::user();
            
            if (!$locataire) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            // États des lieux à l'entrée
            $etatsLieuEntree = EtatLieu::where('locataire_id', $locataire->id)
                ->with(['bien'])
                ->orderBy('created_at', 'desc')
                ->get();

            // États des lieux à la sortie
            $etatsLieuSortie = EtatLieuSorti::where('locataire_id', $locataire->id)
                ->with(['bien'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Décoder les champs JSON
            $etatsLieuEntree->transform(function ($etatLieu) {
                return $this->decodeJsonFields($etatLieu);
            });
            
            $etatsLieuSortie->transform(function ($etatLieu) {
                return $this->decodeJsonFields($etatLieu);
            });

            return response()->json([
                'success' => true,
                'message' => 'Tous les états des lieux récupérés avec succès',
                'data' => [
                    'etats_lieu_entree' => $etatsLieuEntree,
                    'etats_lieu_sortie' => $etatsLieuSortie,
                    'count_entree' => $etatsLieuEntree->count(),
                    'count_sortie' => $etatsLieuSortie->count(),
                    'total' => $etatsLieuEntree->count() + $etatsLieuSortie->count()
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