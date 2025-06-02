<?php

namespace App\Http\Controllers;

use App\Models\Locataire;
use App\Models\Paiement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentRecouvrementController extends Controller
{
   public function dashboard()
    {
        Carbon::setLocale('fr');
        $comptable = Auth::guard('comptable')->user();
        $agenceId = $comptable->agence_id;
        
        // Mois en cours
        $currentMonth = now()->format('Y-m');
        
        // Statistiques principales
        $paidThisMonthCount = Paiement::where('comptable_id', $comptable->id)
            ->where('mois_couvert', $currentMonth)
            ->where('statut', 'payé')
            ->count();
            
        $totalLocataires = Locataire::where('agence_id', $agenceId)
            ->where('status', 'Actif')
            ->count();
            
        $paymentPercentage = $totalLocataires > 0 ? round(($paidThisMonthCount / $totalLocataires) * 100) : 0;
        
        $totalAmountThisMonth = Paiement::where('comptable_id', $comptable->id)
            ->where('mois_couvert', $currentMonth)
            ->where('statut', 'payé')
            ->sum('montant');
            
        $averagePayment = $paidThisMonthCount > 0 ? $totalAmountThisMonth / $paidThisMonthCount : 0;
        
        $pendingPaymentsCount = Paiement::where('comptable_id', $comptable->id)
            ->where('mois_couvert', $currentMonth)
            ->where('statut', '!=', 'payé')
            ->count();
            
        $pendingAmount = Paiement::where('comptable_id', $comptable->id)
            ->where('mois_couvert', $currentMonth)
            ->where('statut', '!=', 'payé')
            ->sum('montant');
        
        // Locataires en retard (n'ont pas payé le mois en cours)
        $latePayersCount = Locataire::where('agence_id', $agenceId)
            ->where('status', 'Actif')
            ->whereDoesntHave('paiements', function($query) use ($currentMonth) {
                $query->where('mois_couvert', $currentMonth)
                    ->where('statut', 'payé');
            })
            ->count();
            
        $latePayersPercentage = $totalLocataires > 0 ? round(($latePayersCount / $totalLocataires) * 100) : 0;
        
        // Derniers paiements enregistrés
        $recentPayments = Paiement::with(['locataire', 'locataire.bien'])
            ->where('comptable_id', $comptable->id)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
        
        // Locataires en retard avec détails
        $latePayers = Locataire::with(['bien', 'paiements' => function($query) {
                $query->orderBy('created_at', 'desc')->limit(1);
            }])
            ->where('agence_id', $agenceId)
            ->where('status', 'Actif')
            ->whereDoesntHave('paiements', function($query) use ($currentMonth) {
                $query->where('mois_couvert', $currentMonth)
                    ->where('statut', 'payé');
            })
            ->select('*')
            ->selectRaw('DATEDIFF(NOW(), CASE WHEN (SELECT MAX(created_at) FROM paiements WHERE locataire_id = locataires.id) IS NOT NULL 
                        THEN (SELECT MAX(created_at) FROM paiements WHERE locataire_id = locataires.id) 
                        ELSE DATE_SUB(NOW(), INTERVAL 2 MONTH) END) as days_late')
            ->selectRaw('(SELECT MAX(created_at) FROM paiements WHERE locataire_id = locataires.id) as last_payment_date')
            ->orderBy('days_late', 'desc')
            ->limit(5)
            ->get();

        return view('agent.agent_dashboard', compact(
            'paidThisMonthCount',
            'totalLocataires',
            'paymentPercentage',
            'totalAmountThisMonth',
            'averagePayment',
            'pendingPaymentsCount',
            'pendingAmount',
            'latePayersCount',
            'latePayersPercentage',
            'recentPayments',
            'latePayers'
        ));
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

    public function paid()
    {
        Carbon::setLocale('fr');
        $comptable = Auth::guard('comptable')->user();
      // Vérifier si le comptable n'a pas d'agence associée

      // Mois en cours au format 'YYYY-MM' pour la comparaison
        $moisEnCoursFormatDB = now()->format('Y-m');
        
        // Mois en cours au format texte en français (ex: "Janvier 2023")
        $moisEnCoursAffichage = now()->translatedFormat('F Y');
        if (!$comptable || !$comptable->agence) {
            return view('agent.locataire.paid', [
                'locataires' => Locataire::whereNull('agence_id')->paginate(10), // Retourne un paginateur vide
                'moisEnCours' => $moisEnCoursAffichage,
                'error' => 'Aucune agence n\'est associée à votre compte comptable.'
            ]);
        }
    $agence = $comptable->agence;
        
        

        $locataires = Locataire::where('agence_id', $agence->id)
            ->where('status', '!=', 'Pas sérieux')
            ->whereDoesntHave('paiements', function ($query) use ($moisEnCoursFormatDB) {
                $query->where('mois_couvert', $moisEnCoursFormatDB)
                    ->where('statut', 'payé');
            })
            ->with(['bien', 'paiements' => function($query) use ($moisEnCoursFormatDB) {
                $query->where('mois_couvert', $moisEnCoursFormatDB);
            }])
            ->paginate(10);

        return view('agent.locataire.paid', [
            'locataires' => $locataires,
            'moisEnCours' => $moisEnCoursAffichage
        ]);
    }

    public function showGenerateCodePage(Locataire $locataire)
    {
        Carbon::setLocale('fr');
        return view('agent.locataire.generate_code', compact('locataire'));
    }

    public function history(){
         Carbon::setLocale('fr');
        $comptable = Auth::guard('comptable')->user();
         $paiements = Paiement::whereHas('locataire', function($query) use ($comptable) {
            $query->where('agence_id', $comptable->agence_id)->where('comptable_id', $comptable->id);
        })->paginate(10);
        return view('agent.locataire.history', compact('paiements'));
    }

}
