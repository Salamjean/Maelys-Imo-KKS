<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Locataire;
use App\Models\Bien;
use App\Models\Contrat;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContratController extends Controller
{
    public function getInfosContrat(Locataire $locataire)
    {
        // Récupérer le bien associé au locataire
        $bien = $locataire->bien;
        
        if (!$bien) {
            return response()->json([
                'error' => 'Aucun bien associé à ce locataire'
            ], 404);
        }

        return response()->json([
            'locataire' => $locataire,
            'bien' => $bien,
            'agence' => $locataire->agence
        ]);
    }

    public function generateAndAssociateContrat(Request $request, $locataireId)
    {
        try {
            // Récupération de l'agence connectée ou utilisation de l'agence par défaut
            $agence = Auth::guard('agence')->user();
            
            if (!$agence) {
                // Si aucune agence n'est connectée, on utilise l'agence par défaut "Maelys-Imo"
                $agence = Admin::where('name', 'Maelys-Imo')->first();
                
                if (!$agence) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Agence par défaut non trouvée.'
                    ], 404);
                }
            }
    
            // Récupération du locataire
            $locataire = Locataire::with(['agence', 'bien','contrat'])
                ->when($agence->name !== 'Maelys-Imo', function($query) use ($agence) {
                    // Si ce n'est pas Maelys-Imo, on filtre par agence_id
                    return $query->where('agence_id', $agence->id);
                })
                ->findOrFail($locataireId);
    
            // Vérification si un contrat existe déjà
            $contrat = Contrat::where('locataire_id', $locataireId)->first();
            
            if (!$contrat) {
                // Création d'un nouveau contrat si aucun n'existe
                $contrat = new Contrat();
                $contrat->locataire_id = $locataire->id;
                $contrat->date_debut = now();
                $contrat->date_fin = now()->addYear();
                $contrat->loyer_mensuel = $locataire->bien->prix ?? 0;
                $contrat->caution = $locataire->bien->caution ?? 0; // On associe l'agence (soit celle connectée, soit Maelys-Imo)
                $contrat->save();
            }
    
            // Données pour le PDF
            $data = [
                'locataire' => $locataire,
                'contrat' => $contrat,
                'agence' => $agence,
                'bien' => $locataire->bien,
                'date_creation' => now()->format('d/m/Y'),
            ];
    
            // Génération du PDF
            $pdf = PDF::loadView('contrats.template', $data);
            
            // Sauvegarde du PDF dans le disque `public`
            $directory = 'contrats';
            $fileName = 'contrat_'.$locataireId.'_'.time().'.pdf';
            $pdfPath = "$directory/$fileName";
            
            Storage::disk('public')->put($pdfPath, $pdf->output());
            
            if (!Storage::disk('public')->exists($pdfPath)) {
                throw new \Exception("Le fichier n'a pas été créé correctement");
            }
            
            // Mise à jour du contrat
            $contrat->fichier_path = $pdfPath;
            $contrat->save();
            
            // Association au locataire
            $locataire->contrat_id = $contrat->id;
            $locataire->save();
    
            return response()->json([
                'success' => true,
                'message' => 'Contrat généré et associé avec succès!',
                'pdf_url' => asset('storage/'.$pdfPath)
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
    
public function show(Contrat $contrat, Request $request)
{
    $filePath = storage_path('app/public/' . $contrat->fichier_path);
    
    if (!file_exists($filePath)) {
        abort(404, "Le fichier contrat n'existe pas");
    }
    
    // Si le paramètre print est présent, forcer le téléchargement
    if ($request->has('print')) {
        return response()->file($filePath, [
            'Content-Disposition' => 'inline; filename="Contrat_'.$contrat->locataire->name.'_'.$contrat->locataire->prenom.'.pdf"'
        ]);
    }
    
    return response()->file($filePath);
}

public function download(Contrat $contrat)
{
    $filePath = storage_path('app/public/' . $contrat->fichier_path);
    
    // Vérification si le fichier existe
    if (!file_exists($filePath)) {
        abort(404, "Le fichier contrat n'existe pas");
    }

    // Téléchargement du fichier
    return response()->download($filePath, 'Contrat_' . $contrat->locataire->name . '_' . $contrat->locataire->prenom . '.pdf');
}

public function downloadContrat(Locataire $locataire)
{
    if (!$locataire->contrat) {
        return back()->with('error', 'Aucun contrat disponible pour ce locataire');
    }

    $path = storage_path('app/public/' . $locataire->contrat);
    
    if (!file_exists($path)) {
        return back()->with('error', 'Le fichier contrat n\'existe pas');
    }

    return response()->download($path, 'contrat_' . $locataire->name . '_' . $locataire->prenom . '.' . pathinfo($path, PATHINFO_EXTENSION));
}
   
}