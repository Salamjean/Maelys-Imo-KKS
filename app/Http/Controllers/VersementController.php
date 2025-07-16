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
            
            $agent->total_verse = Versement::where('agent_id', $agent->id)
                                         ->sum('montant_verse');
            
            $agent->reste_actuel = $agent->total_percu - $agent->total_verse;
        });

        $versements = Versement::with('agent')->latest()->paginate(10);

        return view('comptable.locataire.paid', compact('agents', 'versements'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'agent_id' => 'required|exists:comptables,id',
            'montant' => 'required|numeric|min:1000',
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
                            ->sum('montant_verse');
        
        $reste_avant = $total_percu - $total_verse;

        if ($request->montant > $reste_avant) {
            return back()->withErrors([
                'montant' => 'Le montant ne peut pas dépasser le reste à verser: '.number_format($reste_avant, 0, ',', ' ').' FCFA'
            ]);
        }

        // Enregistrement du versement
        Versement::create([
            'agent_id' => $agent->id,
            'comptable_id' => Auth::guard('comptable')->id(),
            'montant_verse' => $request->montant,
            'montant_percu' => $total_percu,
            'reste_a_verser' => $reste_avant - $request->montant,
        ]);

        return redirect()->route('accounting.versement.history')->with('success', 'Versement enregistré avec succès !');
    }

    public function history()
    {
        $comptable = Auth::guard('comptable')->user();
        $versements = Versement::with('agent')
                        ->where('comptable_id', $comptable->id)
                        ->latest()
                        ->paginate(10);
        return view('comptable.locataire.history', compact('versements'));
    }
}