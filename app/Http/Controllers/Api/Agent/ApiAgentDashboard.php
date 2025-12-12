<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use App\Models\EtatLieu;
use App\Models\Locataire;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiAgentDashboard extends Controller
{
     /**
     * @OA\Get(
     *     path="/api/agent/dashboard",
     *     summary="Récupère les statistiques du tableau de bord de l'agent",
     *     description="Retourne les données statistiques pour le tableau de bord de l'agent connecté",
     *     tags={"Dashboard Agent"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_loyers_percus", type="number", format="float", example=15000.50),
     *                 @OA\Property(property="locataires_a_jour", type="integer", example=15),
     *                 @OA\Property(property="locataires_en_retard", type="integer", example=3),
     *                 @OA\Property(property="paiements_en_attente", type="integer", example=2),
     *                 @OA\Property(property="etats_lieu_effectues", type="integer", example=5),
     *                 @OA\Property(property="mois_courant", type="string", example="2024-01")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Utilisateur non authentifié")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur lors de la récupération des données"),
     *             @OA\Property(property="error", type="string", example="Message d'erreur détaillé")
     *         )
     *     )
     * )
     */
   public function dashboard(Request $request)
    {
        try {
            $comptable = Auth::guard('sanctum')->user();
            
            if (!$comptable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            $currentMonth = now()->format('Y-m');
            
            // 1. Montant total des loyers perçus (ce mois)
            $totalLoyersPerçus = Paiement::where('comptable_id', $comptable->id)
                ->where('mois_couvert', $currentMonth)
                ->where('statut', 'payé')
                ->sum('montant');
            
            // 2. Nombre de locataires à jour
            $locatairesAJour = Locataire::where(function($query) use ($comptable) {
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
                ->count();
            
            // 3. Nombre de locataires en retard
            $locatairesEnRetard = Locataire::where(function($query) use ($comptable) {
                    if ($comptable->agence_id) {
                        $query->orWhere('agence_id', $comptable->agence_id);
                    }
                    if ($comptable->proprietaire_id) {
                        $query->orWhere('proprietaire_id', $comptable->proprietaire_id);
                    }
                })
                ->where('status', 'Actif')
                ->whereDoesntHave('paiements', function($query) use ($currentMonth) {
                    $query->where('mois_couvert', $currentMonth)
                        ->where('statut', 'payé');
                })
                ->count();
            
            // 4. Nombre de paiements en attente
            $paiementsEnAttente = Paiement::where('comptable_id', $comptable->id)
                ->where('mois_couvert', $currentMonth)
                ->where('statut', '!=', 'payé')
                ->count();
            
            // 5. Nombre d'états des lieux effectués (ce mois) - via la relation avec locataire
            $etatsLieuEffectues = EtatLieu::whereHas('locataire', function($query) use ($comptable) {
                    $query->where(function($subQuery) use ($comptable) {
                        if ($comptable->agence_id) {
                            $subQuery->orWhere('agence_id', $comptable->agence_id);
                        }
                        if ($comptable->proprietaire_id) {
                            $subQuery->orWhere('proprietaire_id', $comptable->proprietaire_id);
                        }
                    });
                })
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            // 6. Nombre d'états des lieux en attente (pour les agents de recouvrement seulement)
            $etatsLieuEnAttente = 0; // Initialisation à zéro par défaut

            if ($comptable->user_type === 'Agent de recouvrement') {
                $etatsLieuEnAttente = Locataire::where(function($query) use ($comptable) {
                        if ($comptable->agence_id) {
                            $query->orWhere('agence_id', $comptable->agence_id);
                        }
                        if ($comptable->proprietaire_id) {
                            $query->orWhere('proprietaire_id', $comptable->proprietaire_id);
                        }
                        // On inclut aussi explicitement les locataires du comptable (comme dans ApiAgentEtatLieu)
                        // si les conditions ci-dessus ne sont pas exclusives
                        $query->orWhere('comptable_id', $comptable->id);
                    })
                    ->where('status', 'Actif')
                    ->whereDoesntHave('etatLieu', function($query) {
                        $query->where('status_etat_entre', 'Oui');
                    })
                    ->count();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total_loyers_percus' => (float) $totalLoyersPerçus,
                    'locataires_a_jour' => $locatairesAJour,
                    'locataires_en_retard' => $locatairesEnRetard,
                    'paiements_en_attente' => $paiementsEnAttente,
                    'etats_lieu_effectues' => $etatsLieuEffectues,
                    'etats_lieu_en_attente' => $etatsLieuEnAttente,
                    'mois_courant' => $currentMonth
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des données',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}