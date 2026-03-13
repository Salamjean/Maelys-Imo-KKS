<?php

namespace App\Http\Controllers\Api\Commercial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Agence;
use App\Models\Proprietaire;
use App\Models\Bien;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * @OA\Tag(
 *     name="Commercial - Dashboard",
 *     description="Tableau de bord général pour le commercial"
 * )
 */
class CommercialDashboardApiController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/commercial/dashboard",
     *      operationId="getCommercialDashboard",
     *      tags={"Commercial - Dashboard"},
     *      summary="Données du tableau de bord",
     *      description="Renvoie les compteurs globaux et les activités récentes (agences et propriétaires ajoutés).",
     *      security={{"sanctum":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="statistics", type="object"),
     *                  @OA\Property(property="recent_activities", type="array", @OA\Items(type="object"))
     *              )
     *          )
     *      )
     * )
     */
    public function index()
    {
        try {
            $commercial = auth()->user();

            if (!$commercial || !($commercial instanceof \App\Models\Commercial)) {
                return response()->json(['error' => 'Accès non autorisé'], 403);
            }

            $commercialId = $commercial->code_id;

            // Statistiques globales
            $statistics = [
                'total_agences' => Agence::where('commercial_id', $commercialId)->count(),
                'total_proprietaires' => Proprietaire::where('commercial_id', $commercialId)->count(),
                'total_biens' => Bien::where('commercial_id', $commercialId)->count(),
            ];

            // Activités récentes (5 dernières agences)
            $recentAgences = Agence::where('commercial_id', $commercialId)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function($item) {
                    return [
                        'type' => 'Agence',
                        'name' => $item->name,
                        'description' => 'Nouvelle agence enregistrée : ' . $item->name,
                        'date' => $item->created_at->format('d/m/Y H:i'),
                        'raw_date' => $item->created_at,
                        'status' => 'success'
                    ];
                });

            // Activités récentes (5 derniers propriétaires)
            $recentProprietaires = Proprietaire::where('commercial_id', $commercialId)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function($item) {
                    return [
                        'type' => 'Propriétaire',
                        'name' => $item->name . ' ' . $item->prenom,
                        'description' => 'Nouveau propriétaire enregistré : ' . $item->name . ' ' . $item->prenom,
                        'date' => $item->created_at->format('d/m/Y H:i'),
                        'raw_date' => $item->created_at,
                        'status' => 'info'
                    ];
                });

            // Fusionner et trier par date décroissante
            $recentActivities = $recentAgences->merge($recentProprietaires)
                ->sortByDesc('raw_date')
                ->take(5)
                ->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'commercial' => [
                        'name' => $commercial->name,
                        'prenom' => $commercial->prenom,
                        'code_id' => $commercial->code_id,
                        'profile_image' => $commercial->profile_image ? asset('storage/' . $commercial->profile_image) : null,
                    ],
                    'statistics' => $statistics,
                    'recent_activities' => $recentActivities
                ]
            ]);

        } catch (Exception $e) {
            Log::error('API Error fetching commercial dashboard: ' . $e->getMessage());
            return response()->json(['error' => 'Une erreur est survenue lors de la récupération du tableau de bord.'], 500);
        }
    }
}
