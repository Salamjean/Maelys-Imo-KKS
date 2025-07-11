<?php

namespace App\Http\Controllers;

use App\Models\Proprietaire;
use App\Models\PaymentPartner;
use App\Models\Visite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ChequeVerificationCode;
use App\Models\Paiement;
use Carbon\Carbon;

class PaymentPartnerController extends Controller
{
   public function createPaymentPartner()
    {
        $agenceId = Auth::guard('agence')->user()->code_id;
        
        // Demandes de visite en attente
        $pendingVisits = Visite::where('statut', 'en attente')
                        ->whereHas('bien', function ($query) use ($agenceId) {
                            $query->where('agence_id', $agenceId);
                        })
                        ->count();
        
        // Récupérer les propriétaires de l'agence avec le montant total des loyers du mois en cours
        $owners = Proprietaire::where('agence_id', $agenceId)
                    ->with(['biens' => function($query) {
                        $query->with(['paiements' => function($q) {
                            $currentMonth = now()->format('Y-m');
                            $q->where('mois_couvert', $currentMonth)
                            ->where('statut', 'payé');
                        }]);
                    }])
                    ->paginate(10);
        
        // Calculer le montant total et vérifier si déjà payé pour chaque propriétaire
        $currentMonth = now()->format('Y-m');
            $owners->each(function ($owner) use ($currentMonth, $agenceId) {
            // Calcul du montant total brut
            $montantTotal = $owner->biens->flatMap(function ($bien) {
                return $bien->paiements;
            })->sum('montant');

            // Application du pourcentage
            $pourcentage = is_numeric($owner->pourcentage) ? (float) $owner->pourcentage : 0;
            $owner->montant_total = $montantTotal * (1 - ($pourcentage / 100));

            // Vérifier si un paiement a déjà été effectué ce mois-ci
            $owner->deja_paye = PaymentPartner::where('proprietaire_id', $owner->code_id)
                ->where('agence_id', $agenceId)
                ->where('created_at', 'like', $currentMonth.'%')
                ->exists();
        });
        
        return view('agence.proprietaire.partner.create', compact('pendingVisits', 'owners'));
    }

    public function indexPaymentPartner()
    {
        Carbon::setLocale('fr');
        $agenceId = Auth::guard('agence')->user()->code_id;
        $pendingVisits = Visite::where('statut', 'en attente')
                        ->whereHas('bien', function ($query) use ($agenceId) {
                            $query->where('agence_id', $agenceId);
                        })
                        ->count();
        $paiements = PaymentPartner::where('agence_id', $agenceId)
                        ->with('proprietaire')
                        ->paginate(10);
        return view('agence.proprietaire.partner.index', compact('pendingVisits', 'paiements'));
    }

   public function showPaymentForm($proprietaireId)
    {
        $agenceId = Auth::guard('agence')->user()->code_id;
        $pendingVisits = Visite::where('statut', 'en attente')
                        ->whereHas('bien', function ($query) use ($agenceId) {
                            $query->where('agence_id', $agenceId);
                        })
                        ->count();
        
        $proprietaire = Proprietaire::where('code_id', $proprietaireId)
                        ->with(['biens' => function($query) {
                            $query->with(['paiements' => function($q) {
                                $currentMonth = now()->format('Y-m');
                                $q->where('mois_couvert', $currentMonth)
                                ->where('statut', 'payé');
                            }]);
                        }])
                        ->firstOrFail();
        
        // Calculer le montant total
        $montantTotal = $proprietaire->biens->flatMap(function ($bien) {
            return $bien->paiements;
        })->sum('montant');
        
        return view('agence.proprietaire.partner.payment_form', compact('proprietaire', 'pendingVisits', 'montantTotal'));
    }
    public function storePayment(Request $request)
{
    $request->validate([
        'proprietaire_id' => 'required|exists:proprietaires,code_id',
        'mode_paiement' => 'required|in:Chèques,Virement Bancaire',
        'montant' => 'required|numeric|min:0',
        'fichier_paiement' => 'required_if:mode_paiement,Virement Bancaire|file|mimes:pdf|max:2048',
        'beneficiaire_nom' => 'required_if:est_proprietaire,0',
        'beneficiaire_prenom' => 'required_if:est_proprietaire,0',
        'beneficiaire_contact' => 'required_if:est_proprietaire,0',
        'beneficiaire_email' => 'nullable|email|required_if:est_proprietaire,0',
        'numero_cni' => 'nullable|string|required_if:est_proprietaire,0',
    ]);

    $agenceId = Auth::guard('agence')->user()->code_id;
    $proprietaire = Proprietaire::where('code_id', $request->proprietaire_id)->firstOrFail();

    $data = [
        'proprietaire_id' => $request->proprietaire_id,
        'agence_id' => $agenceId,
        'mode_paiement' => $request->mode_paiement,
        'montant' => $request->montant,
        'est_proprietaire' => $request->boolean('est_proprietaire'),
        'rib' => $proprietaire->rib,
        'statut' => $request->mode_paiement === 'Virement Bancaire' ? 'payé' : 'en attente',
    ];

    if ($request->mode_paiement === 'Virement Bancaire') {
        if ($request->hasFile('fichier_paiement')) {
            $path = $request->file('fichier_paiement')->store('paiements/virements', 'public');
            $data['fichier_paiement'] = $path;
        }
    } else {
        if ($request->boolean('est_proprietaire')) {
            $data['beneficiaire_nom'] = $proprietaire->name;
            $data['beneficiaire_prenom'] = $proprietaire->prenom;
            $data['beneficiaire_contact'] = $proprietaire->contact;
            $data['beneficiaire_email'] = $proprietaire->email;
            $data['statut'] = 'payé';
        } else {
            $data['beneficiaire_nom'] = $request->beneficiaire_nom;
            $data['beneficiaire_prenom'] = $request->beneficiaire_prenom;
            $data['beneficiaire_contact'] = $request->beneficiaire_contact;
            $data['beneficiaire_email'] = $request->beneficiaire_email;
            $data['numero_cni'] = $request->numero_cni;
            
            // Générer un code de vérification
            $verificationCode = Str::upper(Str::random(6));
            $data['verification_code'] = $verificationCode;
            
            // Envoyer le code par email
            Mail::to($proprietaire->email)->send(new ChequeVerificationCode($verificationCode));
        }
    }

    PaymentPartner::create($data);

    // Marquer les paiements comme traités (optionnel)
    $currentMonth = now()->format('Y-m');
    Paiement::whereHas('bien', function($query) use ($proprietaire) {
            $query->where('proprietaire_id', $proprietaire->code_id);
        })
        ->where('mois_couvert', $currentMonth)
        ->where('statut', 'payé');

    return redirect()->route('partner.payment.index')
        ->with('success', 'Paiement enregistré avec succès! ' . 
              ($request->mode_paiement === 'Chèques' && !$request->boolean('est_proprietaire') ? 
              'Un code de vérification a été envoyé au propriétaire.' : ''));
}

    public function showValidationForm($paymentId)
    {
        $payment = PaymentPartner::findOrFail($paymentId);
        $agenceId = Auth::guard('agence')->user()->code_id;

        if ($payment->agence_id !== $agenceId || $payment->statut !== 'en attente') {
            abort(403);
        }

        $pendingVisits = Visite::where('statut', 'en attente')
                        ->whereHas('bien', function ($query) use ($agenceId) {
                            $query->where('agence_id', $agenceId);
                        })
                        ->count();

        return view('agence.proprietaire.partner.validate', compact('payment', 'pendingVisits'));
    }

    public function validatePaymentCode(Request $request, $paymentId)
    {
        $request->validate([
            'verification_code' => 'required|string|size:6'
        ]);

        $payment = PaymentPartner::findOrFail($paymentId);
        $agenceId = Auth::guard('agence')->user()->code_id;

        if ($payment->agence_id !== $agenceId) {
            abort(403);
        }

        if ($payment->verification_code === $request->verification_code) {
            $payment->update([
                'statut' => 'payé',
                'code_valide_par' => Auth::guard('agence')->user()->name,
                'date_validation' => now()
            ]);

            return redirect()->route('partner.payment.index')
                ->with('success', 'Paiement validé avec succès!');
        }

        return back()->with('error', 'Code de vérification incorrect.');
    }
}