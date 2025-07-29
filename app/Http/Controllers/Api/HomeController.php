<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContactMail;
use Illuminate\Http\Request;
use App\Models\Bien;
use App\Models\Proprietaire;
use App\Models\Agence;
use Illuminate\Support\Facades\Mail;

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
                'message' => 'Erreur lors de la récupération des appartements',
                'error' => $e->getMessage()
            ], 500);
        }
    }

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
                'message' => 'Erreur lors de la récupération des maisons',
                'error' => $e->getMessage()
            ], 500);
        }
    }

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
}