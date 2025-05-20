<?php

namespace App\Http\Controllers;

use App\Mail\CancelVisite;
use App\Mail\ConfirmVisite;
use App\Mail\DoneVisite;
use App\Mail\VisiteConfirmation;
use App\Models\Bien;
use App\Models\Visite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class VisiteController extends Controller
{
    public function adminIndex()
    {
        $visites = Visite::where('statut', '!=', 'effectuée')
                        ->where('statut', '!=', 'annulée')
                        ->whereHas('bien', function ($query) {
                            $query->whereNull('agence_id'); 
                        })
                        ->paginate(10);
        
        return view('admin.visites.index', compact('visites'));
    }    
    public function indexAgence()
    {
        $agenceId = Auth::guard('agence')->user()->id;
        $visites = Visite::where('statut', '!=', 'effectuée')
                        ->where('statut', '!=', 'annulée')
                        ->whereHas('bien', function ($query) use ($agenceId) {
                            $query->where('agence_id', $agenceId);  // Filtrer par l'ID de l'agence
                        })
                        ->paginate(10);
        return view('agence.visites.index', compact('visites'));
    }
    public function done()
    {
        $agenceId = Auth::guard('agence')->user()->id;
        
        $visites = Visite::where(function($query) {
                        $query->where('statut', 'effectuée')
                            ->orWhere('statut', 'annulée');
                    })
                    ->whereHas('bien', function ($query) use ($agenceId) {
                        $query->where('agence_id', $agenceId);
                    })
                    ->paginate(10);
        
        return view('agence.visites.done', compact('visites'));
    }
    public function doneAdmin()
    {
        $visites = Visite::where('statut','effectuée')
                        ->where('statut', 'annulée')
                       ->whereHas('bien', function ($query) {
                            $query->whereNull('agence_id'); 
                        })
                        ->paginate(10);
        return view('admin.visites.done', compact('visites'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bien_id' => 'required|exists:biens,id',
            'nom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telephone' => 'required|string|max:20',
            'date_visite' => 'required|date|after_or_equal:today',
            'heure_visite' => 'required',
            'message' => 'nullable|string',
            'statut' => 'in:en attente,confirmée,effectuée,annulée'
        ]);

        // Créer la visite
        $visite = Visite::create($validated);

        // Récupérer les infos du bien
        $bien = Bien::find($validated['bien_id']);

        // Envoyer un email de confirmation (optionnel)
        Mail::to($validated['email'])->send(new VisiteConfirmation($visite, $bien));

        return redirect()->back()->with('success', 'Votre demande de visite a été enregistrée avec succès. Nous vous contacterons pour confirmation.');
    }

    public function confirm(Visite $visite)
    {
        $visite->statut = 'confirmée';
        $visite->save();

        // Envoyer un email de confirmation
        Mail::to($visite->email)->send(new ConfirmVisite($visite, $visite->bien));

        return response()->json(['success' => true]);
    }


    public function markAsDone(Visite $visite)
    {
        $visite->statut = 'effectuée';
        $visite->save();

        // Envoyer un email d'effectuation 
        Mail::to($visite->email)->send(new DoneVisite($visite, $visite->bien));

        return response()->json(['success' => true]);
    }

    public function cancel(Visite $visite)
    {
        $visite->statut = 'annulée';
        $visite->save();

        // Envoyer un email d'annulation 
        Mail::to($visite->email)->send(new CancelVisite($visite, $visite->bien));

        return response()->json(['success' => true]);
    }

    public function show(Visite $visite)
    {
        return response()->json([
            'nom' => $visite->nom,
            'email' => $visite->email,
            'telephone' => $visite->telephone,
            'date_visite' => $visite->date_visite,
            'heure_visite' => $visite->heure_visite,
            'statut' => $visite->statut,
            'message' => $visite->message,
            'bien' => [
                'type' => $visite->bien->type,
                'commune' => $visite->bien->commune,
                'prix' => $visite->bien->prix
            ]
        ]);
    }

    public function updateDate(Request $request, Visite $visite)
{
    $validated = $request->validate([
        'date_visite' => 'required|date',
        'heure_visite' => 'required',
    ]);

    // Sauvegarder l'ancienne date/heure pour l'email
    $oldDate = $visite->date_visite;
    $oldTime = $visite->heure_visite;

    // Mettre à jour la date et l'heure et confirmer la visite
    $visite->date_visite = $validated['date_visite'];
    $visite->heure_visite = $validated['heure_visite'];
    $visite->statut = 'confirmée';
    $visite->save();

    // Envoyer un email au client
    $this->sendVisitUpdateEmail($visite, $oldDate, $oldTime);

    return response()->json(['success' => true]);
}
    public function updateDateAdmin(Request $request, Visite $visite)
{
    $validated = $request->validate([
        'date_visite' => 'required|date',
        'heure_visite' => 'required',
    ]);

    // Sauvegarder l'ancienne date/heure pour l'email
    $oldDate = $visite->date_visite;
    $oldTime = $visite->heure_visite;

    // Mettre à jour la date et l'heure et confirmer la visite
    $visite->date_visite = $validated['date_visite'];
    $visite->heure_visite = $validated['heure_visite'];
    $visite->statut = 'confirmée';
    $visite->save();

    // Envoyer un email au client
    $this->sendVisitUpdateEmail($visite, $oldDate, $oldTime);

    return response()->json(['success' => true]);
}

protected function sendVisitUpdateEmail($visite, $oldDate, $oldTime)
{
    $details = [
        'subject' => 'Modification de votre visite',
        'to' => $visite->email,
        'nom' => $visite->nom,
        'old_date' => \Carbon\Carbon::parse($oldDate)->format('d/m/Y'),
        'old_time' => $oldTime,
        'new_date' => \Carbon\Carbon::parse($visite->date_visite)->format('d/m/Y'),
        'new_time' => $visite->heure_visite,
        'bien' => $visite->bien->type . ' à ' . $visite->bien->commune
    ];

    // Utilisez votre système d'envoi d'email (Mail, Notification, etc.)
    Mail::to($details['to'])->send(new \App\Mail\VisitUpdated($details));
}

//fonction des visites gerer par l'administrateur 
 public function allVisit(){
    $visites = Visite::paginate(10);
    return view('admin.visites.allList', compact('visites'));
 }

  public function adminConfirm(Visite $visite)
    {
        $visite->statut = 'confirmée';
        $visite->save();

        // Envoyer un email de confirmation
        Mail::to($visite->email)->send(new ConfirmVisite($visite, $visite->bien));

        return response()->json(['success' => true]);
    }


    public function adminMarkAsDone(Visite $visite)
    {
        $visite->statut = 'effectuée';
        $visite->save();

        // Envoyer un email d'effectuation 
        Mail::to($visite->email)->send(new DoneVisite($visite, $visite->bien));

        return response()->json(['success' => true]);
    }

    public function adminCancel(Visite $visite)
    {
        $visite->statut = 'annulée';
        $visite->save();

        // Envoyer un email d'annulation 
        Mail::to($visite->email)->send(new CancelVisite($visite, $visite->bien));

        return response()->json(['success' => true]);
    }

    public function adminShow(Visite $visite)
    {
        return response()->json([
            'nom' => $visite->nom,
            'email' => $visite->email,
            'telephone' => $visite->telephone,
            'date_visite' => $visite->date_visite,
            'heure_visite' => $visite->heure_visite,
            'statut' => $visite->statut,
            'message' => $visite->message,
            'bien' => [
                'type' => $visite->bien->type,
                'commune' => $visite->bien->commune,
                'prix' => $visite->bien->prix
            ]
        ]);
    }
 
}
