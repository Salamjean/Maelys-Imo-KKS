<?php

namespace App\Http\Controllers;

use App\Models\Comptable;
use App\Models\Versement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VersementController extends Controller
{
   public function paid()
    {
        // Récupère uniquement les agents (user_type = "Agent de recouvrement")
        $agents = Comptable::where('user_type', 'Agent de recouvrement')->get();
        $versements = Versement::with('agent')
                  ->latest()
                  ->paginate(10);

        return view('comptable.locataire.paid', compact('agents', 'versements'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'agent_id' => 'required|exists:comptables,id',
            'montant'  => 'required|numeric|min:1000', // Ex: Montant minimum 1000 FCFA
        ]);

        // Vérifie que l'ID fourni correspond bien à un agent
        $agent = Comptable::findOrFail($request->agent_id);
        if (!$agent->isAgent()) {
            return back()->withErrors(['agent_id' => 'L\'utilisateur sélectionné n\'est pas un agent !']);
        }

        // Enregistrement du versement
        Versement::create([
            'agent_id'      => $request->agent_id,
            'comptable_id' => Auth::guard('comptable')->user()->id, // ID du comptable connecté
            'montant'      => $request->montant,
        ]);

        return back()->with('success', 'Versement enregistré avec succès !');
    }

}
