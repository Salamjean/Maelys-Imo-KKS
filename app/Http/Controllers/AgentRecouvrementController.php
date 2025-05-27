<?php

namespace App\Http\Controllers;

use App\Models\Locataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentRecouvrementController extends Controller
{
    public function dashboard()
    {
        return view('agent.agent_dashboard');
    }

    public function tenant() {
        // Récupérer le comptable connecté
        $comptable = Auth::guard('comptable')->user();
        
        // Vérifier si le comptable a une agence associée
        if ($comptable->agence_id) {
            // Récupérer les locataires avec leurs relations
            $locataires = Locataire::with(['bien', 'paiements', 'agence'])
                ->where('agence_id', $comptable->agence_id)
                ->get();
        } else {
            // Si le comptable n'a pas d'agence, retourner une collection vide
            $locataires = collect();
        }
        
        return view('agent.locataire.index', compact('locataires'));
    }

   public function payment() {
        // Récupérer le comptable connecté
        $comptable = Auth::guard('comptable')->user();

        // Récupérer l'agence du comptable
        $agence = $comptable->agence;

        // Récupération des locataires de l'agence du comptable
        $locataires = Locataire::with(['bien', 'paiements' => function($query) {
            $query->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        }])
        ->where('status', '!=', 'Pas sérieux')
        ->where('agence_id', $agence->id)
        ->paginate(6);

        // Ajout d'une propriété à chaque locataire pour afficher ou non le bouton
        $locataires->getCollection()->transform(function($locataire) {
            $today = now()->format('d');
            $currentMonthPaid = $locataire->paiements->isNotEmpty();
            $locataire->show_reminder_button = ($locataire->bien->date_fixe == $today) && !$currentMonthPaid;
            return $locataire;
        });

        return view('agent.locataire.paiement', compact('locataires'));
    }

}
