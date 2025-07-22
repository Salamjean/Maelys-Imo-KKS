<?php

namespace App\Http\Controllers;

use App\Models\Agence;
use App\Models\Bien;
use App\Models\EtatLieu;
use App\Models\EtatLieuSorti;
use Illuminate\Http\Request;
use App\Models\Locataire; // Supposons que vous avez un modèle Locataire
use App\Models\Proprietaire;
use App\Models\VerificationCode;
use Carbon\Carbon;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use PDF;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EtatAgentLocataire extends Controller
{
    public function currentSituation(){
        
        // Récupérer l'agent connecté (à adapter selon votre système d'authentification)
        $agent = Auth::user();
        
        // Récupérer les locataires associés à cet agent
        $locataires = Locataire::where('comptable_id', $agent->id)->get();
        
        return view('agent.etat_lieu.type_etat', [
            'locataires' => $locataires,
            'title' => 'ETAT DES LIEUX'
        ]);
    }


    // Dans EtatLieuController.php
public function create($locataire_id)
{
    // Récupérer le locataire avec son bien
    $locataire = Locataire::with('bien')->findOrFail($locataire_id);
    
    if (!$locataire->bien) {
            return back()->with('error', 'Ce locataire n\'a pas de bien associé.');
        }
    
    return view('agent.etat_lieu.etat_entree', [
        'bien' => $locataire->bien,
        'locataire' => $locataire,
    ]);
}

  public function store(Request $request)
{
    // Validation des données
    $validated = $request->validate([
        'locataire_id' => 'required|exists:locataires,id',
        'bien_id' => 'required|exists:biens,id',
        'type_bien' => 'nullable|string|max:255',
        'commune_bien' => 'nullable|string|max:255',
        'presence_partie' => 'required|in:oui,non',
        'status_etat_entre' => 'nullable|string',
        
        // Parties communes
        'parties_communes.sol' => 'nullable|string',
        'parties_communes.observation_sol' => 'nullable|string',
        'parties_communes.murs' => 'nullable|string',
        'parties_communes.observation_murs' => 'nullable|string',
        'parties_communes.plafond' => 'nullable|string',
        'parties_communes.observation_plafond' => 'nullable|string',
        'parties_communes.porte_entre' => 'nullable|string',
        'parties_communes.observation_porte_entre' => 'nullable|string',
        'parties_communes.interrupteur' => 'nullable|string',
        'parties_communes.observation_interrupteur' => 'nullable|string',
        'parties_communes.robinet' => 'nullable|string',
        'parties_communes.observation_robinet' => 'nullable|string',
        'parties_communes.lavabo' => 'nullable|string',
        'parties_communes.observation_lavabo' => 'nullable|string',
        'parties_communes.douche' => 'nullable|string',
        'parties_communes.observation_douche' => 'nullable|string',
        
        // Chambres
        'chambres' => 'required|array|min:1',
        'chambres.*.nom' => 'required|string',
        'chambres.*.sol' => 'nullable|string',
        'chambres.*.observation_sol' => 'nullable|string',
        'chambres.*.murs' => 'nullable|string',
        'chambres.*.observation_murs' => 'nullable|string',
        'chambres.*.plafond' => 'nullable|string',
        'chambres.*.observation_plafond' => 'nullable|string',
        
        'nombre_cle' => 'required|integer|min:1',
    ]);

    // Préparation des données
    $data = [
        'locataire_id' => $validated['locataire_id'],
        'bien_id' => $validated['bien_id'],
        'type_bien' => $validated['type_bien'] ?? null,
        'commune_bien' => $validated['commune_bien'] ?? null,
        'presence_partie' => $validated['presence_partie'],
        'status_etat_entre' =>'En attente',
        'parties_communes' => json_encode($validated['parties_communes']),
        'chambres' => json_encode($validated['chambres']),
        'nombre_cle' => $validated['nombre_cle'],
    ];

    // Création de l'état des lieux
    EtatLieu::create($data);

    return redirect()->route('accounting.current', $validated['locataire_id'])
                    ->with('success', 'État des lieux enregistré avec succès.');
}

public function download($id)
{
    $etatLieu = EtatLieu::with(['locataire', 'bien'])->findOrFail($id);
    
    // Décoder les JSON
    $etatLieu->parties_communes = json_decode($etatLieu->parties_communes, true);
    $etatLieu->chambres = json_decode($etatLieu->chambres, true);
    
    $pdf = PDF::loadView('agent.etat_lieu.pdf', compact('etatLieu'));
    
    return $pdf->download('etat-lieux-'.$etatLieu->locataire->name.'-'.$etatLieu->created_at->format('d-m-Y').'.pdf');
}


public function sortie($locataire_id)
{
    // Récupérer le locataire avec son bien
    $locataire = Locataire::with('bien')->findOrFail($locataire_id);
    
    if (!$locataire->bien) {
        return back()->with('error', 'Ce locataire n\'a pas de bien associé.');
    }
    
    // Récupérer l'état des lieux d'entrée (le plus récent)
    $etatEntree = EtatLieu::where('locataire_id', $locataire_id)
                          ->whereNotNull('status_etat_entre')
                          ->latest()
                          ->first();
    
    if (!$etatEntree) {
        return back()->with('error', 'Aucun état des lieux d\'entrée trouvé pour ce locataire.');
    }
    if ($etatEntree && $etatEntree->parties_communes) {
        $etatEntree->parties_communes = json_decode($etatEntree->parties_communes, true);
    }
    
    return view('agent.etat_lieu.etat_sortie', [
        'bien' => $locataire->bien,
        'etatEntree' => $etatEntree, // État des lieux d'entrée
        'locataire' => $locataire,
    ]);
}


  public function storeSortie(Request $request)
{
    // Validation des données
    $validated = $request->validate([
        'locataire_id' => 'required|exists:locataires,id',
        'bien_id' => 'required|exists:biens,id',
        'type_bien' => 'nullable|string|max:255',
        'commune_bien' => 'nullable|string|max:255',
        'presence_partie' => 'required|in:oui,non',
        'status_etat_entre' => 'nullable|string',
        
        // Parties communes
        'parties_communes.sol' => 'nullable|string',
        'parties_communes.observation_sol' => 'nullable|string',
        'parties_communes.murs' => 'nullable|string',
        'parties_communes.observation_murs' => 'nullable|string',
        'parties_communes.plafond' => 'nullable|string',
        'parties_communes.observation_plafond' => 'nullable|string',
        'parties_communes.porte_entre' => 'nullable|string',
        'parties_communes.observation_porte_entre' => 'nullable|string',
        'parties_communes.interrupteur' => 'nullable|string',
        'parties_communes.observation_interrupteur' => 'nullable|string',
        'parties_communes.robinet' => 'nullable|string',
        'parties_communes.observation_robinet' => 'nullable|string',
        'parties_communes.lavabo' => 'nullable|string',
        'parties_communes.observation_lavabo' => 'nullable|string',
        'parties_communes.douche' => 'nullable|string',
        'parties_communes.observation_douche' => 'nullable|string',
        
        // Chambres
        'chambres' => 'required|array|min:1',
        'chambres.*.nom' => 'required|string',
        'chambres.*.sol' => 'nullable|string',
        'chambres.*.observation_sol' => 'nullable|string',
        'chambres.*.murs' => 'nullable|string',
        'chambres.*.observation_murs' => 'nullable|string',
        'chambres.*.plafond' => 'nullable|string',
        'chambres.*.observation_plafond' => 'nullable|string',
        
        'nombre_cle' => 'required|integer|min:1',
    ]);

    // Préparation des données
    $data = [
        'locataire_id' => $validated['locataire_id'],
        'bien_id' => $validated['bien_id'],
        'type_bien' => $validated['type_bien'] ?? null,
        'commune_bien' => $validated['commune_bien'] ?? null,
        'presence_partie' => $validated['presence_partie'],
        'status_sorti' => 'En attente',
        'parties_communes' => json_encode($validated['parties_communes']),
        'chambres' => json_encode($validated['chambres']),
        'nombre_cle' => $validated['nombre_cle'],
    ];

    // Création de l'état des lieux
    EtatLieuSorti::create($data);

    return redirect()->route('accounting.current', $validated['locataire_id'])
                    ->with('success', 'État des lieux enregistré avec succès.');
}

public function downloadSortie($id)
{
    $etatLieuSorti = EtatLieuSorti::with(['locataire', 'bien'])->findOrFail($id);
    
    // Décoder les JSON
    $etatLieuSorti->parties_communes = json_decode($etatLieuSorti->parties_communes, true);
    $etatLieuSorti->chambres = json_decode($etatLieuSorti->chambres, true);
    
    $pdf = PDF::loadView('agent.etat_lieu.pdf_sorti', compact('etatLieuSorti'));
    
    return $pdf->download('etat-lieux-'.$etatLieuSorti->locataire->name.'-'.$etatLieuSorti->created_at->format('d-m-Y').'.pdf');
}
 
}