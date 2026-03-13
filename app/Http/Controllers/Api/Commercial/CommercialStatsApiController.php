<?php

namespace App\Http\Controllers\Api\Commercial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Agence;
use App\Models\Proprietaire;
use App\Models\Bien;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * @OA\Tag(
 *     name="Commercial - Statistiques",
 *     description="Statistiques d'activité pour le commercial"
 * )
 */
class CommercialStatsApiController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/commercial/statistics",
     *      operationId="getCommercialStatistics",
     *      tags={"Commercial - Statistiques"},
     *      summary="Récupérer les statistiques du commercial",
     *      description="Renvoie les statistiques quotidiennes, globales et l'historique des 7 derniers jours.",
     *      security={{"sanctum":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="daily", type="object"),
     *                  @OA\Property(property="totals", type="object"),
     *                  @OA\Property(property="history", type="array", @OA\Items(type="object")),
     *                  @OA\Property(property="chart_data", type="object")
     *              )
     *          )
     *      )
     * )
     */
    public function index()
    {
        try {
            Carbon::setLocale('fr');
            $commercial = auth()->user();

            if (!$commercial || !($commercial instanceof \App\Models\Commercial)) {
                return response()->json(['error' => 'Accès non autorisé'], 403);
            }

            $today = Carbon::today();
            $commercialId = $commercial->code_id;

            // Statistiques du jour
            $daily = [
                'agences' => Agence::where('commercial_id', $commercialId)->whereDate('created_at', $today)->count(),
                'proprietaires' => Proprietaire::where('commercial_id', $commercialId)->whereDate('created_at', $today)->count(),
                'biens' => Bien::where('commercial_id', $commercialId)->whereDate('created_at', $today)->count(),
            ];

            // Statistiques globales
            $totals = [
                'agences' => Agence::where('commercial_id', $commercialId)->count(),
                'proprietaires' => Proprietaire::where('commercial_id', $commercialId)->count(),
                'biens' => Bien::where('commercial_id', $commercialId)->count(),
            ];

            // Historique journalier (7 derniers jours)
            $history = [];
            $statsForChart = [
                'labels' => [],
                'agences' => [],
                'proprietaires' => [],
                'biens' => []
            ];

            for ($i = 0; $i < 7; $i++) {
                $date = Carbon::today()->subDays($i);
                $agencesCount = Agence::where('commercial_id', $commercialId)->whereDate('created_at', $date)->count();
                $proprietairesCount = Proprietaire::where('commercial_id', $commercialId)->whereDate('created_at', $date)->count();
                $biensCount = Bien::where('commercial_id', $commercialId)->whereDate('created_at', $date)->count();

                $history[] = [
                    'date' => $date->translatedFormat('d F Y'),
                    'agences' => $agencesCount,
                    'proprietaires' => $proprietairesCount,
                    'biens' => $biensCount,
                ];

                // Pour le graphique (ordre chronologique inverse pour la boucle, mais on veut chronologique pour le front)
                // Donc on insère au début
                array_unshift($statsForChart['labels'], $date->translatedFormat('D d M'));
                array_unshift($statsForChart['agences'], $agencesCount);
                array_unshift($statsForChart['proprietaires'], $proprietairesCount);
                array_unshift($statsForChart['biens'], $biensCount);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'daily' => $daily,
                    'totals' => $totals,
                    'history' => $history,
                    'chart_data' => $statsForChart,
                    'commercial' => [
                        'name' => $commercial->name,
                        'prenom' => $commercial->prenom,
                        'code_id' => $commercial->code_id
                    ]
                ]
            ]);

        } catch (Exception $e) {
            Log::error('API Error fetching commercial stats: ' . $e->getMessage());
            return response()->json(['error' => 'Une erreur est survenue lors de la récupération des statistiques.'], 500);
        }
    }
}
