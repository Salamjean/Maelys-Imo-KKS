<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bien;
use App\Models\Proprietaire;
use App\Models\Agence;

class HomeController extends Controller
{
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
}