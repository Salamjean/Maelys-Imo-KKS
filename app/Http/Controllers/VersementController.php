<?php

namespace App\Http\Controllers;

use App\Models\Comptable;
use App\Models\Paiement;
use App\Models\Versement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VersementController extends Controller
{
   public function paid()
{
    $comptable = Auth::guard('comptable')->user();
    $agenceId = $comptable->agence_id;

    // Récupère les agents
    $agents = $comptable->agence_id 
        ? Comptable::where('user_type', 'Agent de recouvrement')->where('agence_id', $agenceId)->get()
        : Comptable::where('user_type', 'Agent de recouvrement')->whereNull('agence_id')->get();

    // Calcule les totaux pour chaque agent
    $agents->each(function($agent) {
        $agent->total_percu = Paiement::where('comptable_id', $agent->id)
                                    ->where('statut', 'payé')
                                    ->sum('montant');
        
        // CHANGEMENT ICI : utiliser 'montant' au lieu de 'montant_verse'
        $agent->total_verse = Versement::where('agent_id', $agent->id)
                                     ->sum('montant');
        
        $agent->reste_actuel = $agent->total_percu - $agent->total_verse;
    });

    $versements = Versement::with('agent')->latest()->paginate(10);

    return view('comptable.locataire.paid', compact('agents', 'versements'));
}

   public function store(Request $request)
    {
        $request->validate([
            'agent_id' => 'required|exists:comptables,id',
            'montant' => 'required|numeric|min:1000', // Changé de montant_verse à montant
        ]);

        $agent = Comptable::findOrFail($request->agent_id);
        if (!$agent->isAgent()) {
            return back()->withErrors(['agent_id' => 'L\'utilisateur sélectionné n\'est pas un agent !']);
        }

        // Calcul des totaux
        $total_percu = Paiement::where('comptable_id', $agent->id)
                            ->where('statut', 'payé')
                            ->sum('montant');
        
        $total_verse = Versement::where('agent_id', $agent->id)
                            ->sum('montant'); // Changé de montant_verse à montant
        
        $reste_avant = $total_percu - $total_verse;

        if ($request->montant > $reste_avant) { // Changé de montant_verse à montant
            return back()->withErrors([
                'montant' => 'Le montant ne peut pas dépasser le reste à verser: '.number_format($reste_avant, 0, ',', ' ').' FCFA'
            ]);
        }

        // Enregistrement du versement
        $versement = new Versement();
        $versement->agent_id = $agent->id;
        $versement->comptable_id = Auth::guard('comptable')->id();
        $versement->montant = $request->montant; // Changé de montant_verse à montant
        $versement->montant_percu = $total_percu; // Total des paiements perçus par l'agent
        $versement->reste_a_verser = $reste_avant - $request->montant; // Reste après ce versement
        $versement->save();

        return redirect()->route('accounting.versement.history')->with('success', 'Versement enregistré avec succès !');
    }

    public function history(){
        // Vérifie si l'utilisateur est authentifié en tant que comptable
        $comptable = Auth::guard('comptable')->user();
        $versements = Versement::with('agent')
                        ->where('comptable_id', $comptable->id)
                        ->latest()
                        ->paginate(10);
        return view('comptable.locataire.history', compact('versements'));
    }

}
