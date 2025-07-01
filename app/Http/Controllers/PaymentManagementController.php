<?php

namespace App\Http\Controllers;

use App\Models\Locataire;
use App\Models\Paiement;
use App\Models\Visite;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentManagementController extends Controller
{
    public function indexAgence(){
        $agenceId = Auth::guard('agence')->user()->code_id;
        // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')
                        ->whereHas('bien', function ($query) use ($agenceId) {
                            $query->where('agence_id', $agenceId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        Carbon::setLocale('fr');
        $paiements = Paiement::whereHas('bien', function ($query) use ($agenceId) {
                            $query->where('agence_id', $agenceId);  // Filtrer par l'ID de l'agence
                        })
                        ->paginate(10);
        return view('agence.paiement.index',compact('paiements','pendingVisits'));
    }

    public function validatePayment(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:paiements,id',
        ]);

        $paiement = Paiement::find($request->id);
        $paiement->statut = 'payé';
        $paiement->save();

        return response()->json(['success' => true]);
    }
    public function indexOwner(){
        $ownerId = Auth::guard('owner')->user()->code_id;
     // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')->where('statut', '!=', 'effectuée')
                        ->where('statut', '!=', 'annulée')
                        ->whereHas('bien', function ($query) use ($ownerId) {
                             $query->where('proprietaire_id', $ownerId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        Carbon::setLocale('fr');
        $paiements = Paiement::whereHas('bien', function ($query) use ($ownerId) {
                             $query->where('proprietaire_id', $ownerId);  // Filtrer par l'ID de l'agence
                        })
                        ->paginate(10);
        return view('proprietaire.paiement.index',compact('paiements','pendingVisits'));
    }

    public function validatePaymentOwner(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:paiements,id',
        ]);

        $paiement = Paiement::find($request->id);
        $paiement->statut = 'payé';
        $paiement->save();

        return response()->json(['success' => true]);
    }
    public function indexAdmin(){
      // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')
                        ->whereHas('bien', function ($query) {
                             $query->whereNull('agence_id');  // Filtrer par l'ID de l'agence
                             $query->whereNull('proprietaire_id') // 1er cas: bien sans propriétaire
                                ->orWhereHas('proprietaire', function($q) {
                                    $q->where('gestion', 'agence'); // 2ème cas: bien avec propriétaire gestion agence
                                });
                        })
                        ->count();
        Carbon::setLocale('fr');
        $paiements = Paiement::whereHas('bien', function ($query) {
                             $query->whereNull('agence_id');  // Filtrer par l'ID de l'agence
                             $query->whereNull('proprietaire_id') // 1er cas: bien sans propriétaire
                                ->orWhereHas('proprietaire', function($q) {
                                    $q->where('gestion', 'agence'); // 2ème cas: bien avec propriétaire gestion agence
                                });
                        })
                        ->paginate(10);
        return view('admin.paiement.index',compact('paiements','pendingVisits'));
    }

    public function validatePaymentAdmin(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:paiements,id',
        ]);

        $paiement = Paiement::find($request->id);
        $paiement->statut = 'payé';
        $paiement->save();

        return response()->json(['success' => true]);
    }
}
