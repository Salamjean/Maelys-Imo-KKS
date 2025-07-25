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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VisiteController extends Controller
{
    public function adminIndex()
    {
        $adminId = Auth::guard('admin')->user()->id;
        $visites = Visite::where('statut', '!=', 'effectuée')
                        ->where('statut', '!=', 'annulée')
                        ->whereHas('bien', function ($query) {
                             $query->whereNull('agence_id');  // Filtrer par l'ID de l'agence
                             $query->whereNull('proprietaire_id') // 1er cas: bien sans propriétaire
                                ->orWhereHas('proprietaire', function($q) {
                                    $q->where('gestion', 'agence'); // 2ème cas: bien avec propriétaire gestion agence
                                });
                        })
                        ->paginate(10);
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
        return view('admin.visites.index', compact('visites', 'pendingVisits'));
    }    
    public function ownerIndex()
    {
        
         $ownerId = Auth::guard('owner')->user()->code_id;
     // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')->where('statut', '!=', 'effectuée')
                        ->where('statut', '!=', 'annulée')
                        ->whereHas('bien', function ($query) use ($ownerId) {
                             $query->where('proprietaire_id', $ownerId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        $visites = Visite::where('statut', '!=', 'effectuée')
                        ->where('statut', '!=', 'annulée')
                        ->whereHas('bien', function ($query) use ($ownerId) {
                             $query->where('proprietaire_id', $ownerId);  // Filtrer par l'ID de l'agence
                        })
                        ->paginate(10);
        
        return view('proprietaire.visites.index', compact('visites', 'pendingVisits'));
    }    
    public function indexAgence()
    {
        $agenceId = Auth::guard('agence')->user()->code_id;
        // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')
                        ->whereHas('bien', function ($query) use ($agenceId) {
                            $query->where('agence_id', $agenceId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        $visites = Visite::where('statut', '!=', 'effectuée')
                        ->where('statut', '!=', 'annulée')
                        ->whereHas('bien', function ($query) use ($agenceId) {
                            $query->where('agence_id', $agenceId);  // Filtrer par l'ID de l'agence
                        })
                        ->paginate(10);
        return view('agence.visites.index', compact('visites', 'pendingVisits'));
    }
    public function done()
    {
        $agenceId = Auth::guard('agence')->user()->code_id;
        // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')
                        ->whereHas('bien', function ($query) use ($agenceId) {
                            $query->where('agence_id', $agenceId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        
        $visites = Visite::where(function($query) {
                        $query->where('statut', 'effectuée')
                            ->orWhere('statut', 'annulée');
                    })
                    ->whereHas('bien', function ($query) use ($agenceId) {
                        $query->where('agence_id', $agenceId);
                    })
                    ->paginate(10);
        
        return view('agence.visites.done', compact('visites', 'pendingVisits'));
    }
    public function ownerDone()
    {
         $ownerId = Auth::guard('owner')->user()->code_id;
     // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')->where('statut', '!=', 'effectuée')
                        ->where('statut', '!=', 'annulée')
                        ->whereHas('bien', function ($query) use ($ownerId) {
                             $query->where('proprietaire_id', $ownerId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        
        $visites = Visite::where(function($query) {
                        $query->where('statut', 'effectuée')
                            ->orWhere('statut', 'annulée');
                    })
                    ->whereHas('bien', function ($query) use ($ownerId) {
                        $query->where('proprietaire_id', $ownerId);
                    })
                    ->paginate(10);
        
        return view('proprietaire.visites.done', compact('visites', 'pendingVisits'));
    }
    public function doneAdmin()
    {
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
        $adminId = Auth::guard('admin')->user()->id;
        $visites = Visite::whereHas('bien', function ($query) {
                            $query->whereNull('agence_id');  // Filtrer par l'ID de l'agence
                            $query->whereNull('proprietaire_id') // 1er cas: bien sans propriétaire
                            ->orWhereHas('proprietaire', function($q) {
                                $q->where('gestion', 'agence'); // 2ème cas: bien avec propriétaire gestion agence
                            });
                        })
                        ->paginate(10);
        return view('admin.visites.done', compact('visites', 'pendingVisits'));
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
        'message' => 'nullable|string|max:50',
        'statut' => 'in:en attente,confirmée,effectuée,annulée'
    ],[
        'bien_id.required' => 'Le bien est obligatoire.',
        'bien_id.exists' => 'Le bien sélectionné n\'existe pas.',
        'nom.required' => 'Le nom est obligatoire.',
        'email.required' => 'L\'email est obligatoire.',
        'telephone.required' => 'Le téléphone est obligatoire.',
        'date_visite.required' => 'La date de visite est obligatoire.',
        'date_visite.after_or_equal' => 'La date de visite doit être aujourd\'hui ou une date future.',
        'heure_visite.required' => 'L\'heure de visite est obligatoire.',
        'message.max' => 'Le message ne peut pas dépasser 50 caractères.'
    ]);

    // Créer la visite
    $visite = Visite::create($validated);

    // Récupérer les infos du bien
    $bien = Bien::find($validated['bien_id']);

    // Envoyer un email de confirmation
    // Mail::to($validated['email'])->send(new VisiteConfirmation($visite, $bien));

    // Envoyer un SMS via l'API Orange
    $this->sendOrangeSMS(
        $validated['telephone'],
        "Votre demande de visite pour le bien a été enregistrée Merci!"
    );

    return redirect()->route('home')->with('success', 'Votre demande de visite a été enregistrée avec succès. Nous vous contacterons pour confirmation.');
}

private function sendOrangeSMS($recipient, $message)
{
    try {
        // Récupérer le token d'accès
        $tokenResponse = Http::withHeaders([
            'Authorization' => 'Basic ' . env('ORANGE_SMS_AUTHORIZATION_HEADER'),
            'Content-Type' => 'application/x-www-form-urlencoded'
        ])->asForm()->post('https://api.orange.com/oauth/v2/token', [
            'grant_type' => 'client_credentials',
            'client_id' => env('ORANGE_SMS_CLIENT_ID'),
            'client_secret' => env('ORANGE_SMS_CLIENT_SECRET')
        ]);

        if ($tokenResponse->successful()) {
            $accessToken = $tokenResponse->json()['access_token'];
            
            // Envoyer le SMS
            $smsResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->post('https://api.orange.com/smsmessaging/v1/outbound/' . urlencode(env('ORANGE_SMS_SENDER_NUMBER')) . '/requests', [
                'outboundSMSMessageRequest' => [
                    'address' => 'tel:+' . preg_replace('/[^0-9]/', '', $recipient),
                    'senderAddress' => 'tel:+' . env('ORANGE_SMS_SENDER_NUMBER'),
                    'outboundSMSTextMessage' => [
                        'message' => $message
                    ]
                ]
            ]);

            if (!$smsResponse->successful()) {
                Log::error('Échec de l\'envoi du SMS Orange: ' . $smsResponse->body());
            }
        } else {
            Log::error('Échec de l\'obtention du token Orange SMS: ' . $tokenResponse->body());
        }
    } catch (\Exception $e) {
        Log::error('Erreur lors de l\'envoi du SMS Orange: ' . $e->getMessage());
    }
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

    public function cancel(Visite $visite, Request $request)
    {
        $visite->statut = 'annulée';
        $visite->motif = $request->motif; // Sauvegarder le motif
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
        'motif' => 'required',
    ]);

    // Sauvegarder l'ancienne date/heure pour l'email
    $oldDate = $visite->date_visite;
    $oldTime = $visite->heure_visite;
    $motif = $visite->motif;

    // Mettre à jour la date et l'heure et confirmer la visite
    $visite->date_visite = $validated['date_visite'];
    $visite->heure_visite = $validated['heure_visite'];
    $visite->motif = $validated['motif'];
    $visite->statut = 'confirmée';
    $visite->save();

    // Envoyer un email au client
    $this->sendVisitUpdateEmail($visite, $oldDate, $oldTime, $motif);

    return response()->json(['success' => true]);
}
    public function updateDateAdmin(Request $request, Visite $visite)
{
    $validated = $request->validate([
        'date_visite' => 'required|date',
        'heure_visite' => 'required',
        'motif' => 'required',
    ]);

    // Sauvegarder l'ancienne date/heure pour l'email
    $oldDate = $visite->date_visite;
    $oldTime = $visite->heure_visite;
    $motif = $visite->motif;

    // Mettre à jour la date et l'heure et confirmer la visite
    $visite->date_visite = $validated['date_visite'];
    $visite->heure_visite = $validated['heure_visite'];
    $visite->motif = $validated['motif'];
    $visite->statut = 'confirmée';
    $visite->save();

    // Envoyer un email au client
    $this->sendVisitUpdateEmail($visite, $oldDate, $oldTime, $motif);

    return response()->json(['success' => true]);
}

    public function updateDateOwner(Request $request, Visite $visite)
{
    $validated = $request->validate([
        'date_visite' => 'required|date',
        'heure_visite' => 'required',
        'motif' => 'required',
    ]);

    // Sauvegarder l'ancienne date/heure pour l'email
    $oldDate = $visite->date_visite;
    $oldTime = $visite->heure_visite;
    $motif = $visite->motif;

    // Mettre à jour la date et l'heure et confirmer la visite
    $visite->date_visite = $validated['date_visite'];
    $visite->heure_visite = $validated['heure_visite'];
    $visite->motif = $validated['motif'];
    $visite->statut = 'confirmée';
    $visite->save();

    // Envoyer un email au client
    $this->sendVisitUpdateEmail($visite, $oldDate, $oldTime, $motif);

    return response()->json(['success' => true]);
}

protected function sendVisitUpdateEmail($visite, $oldDate, $oldTime)
{
    $details = [
        'subject' => 'Modification de votre visite',
        'to' => $visite->email,
        'nom' => $visite->nom,
        'motif' => $visite->motif ?? 'Aucun motif fourni',
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
    $visites = Visite::paginate(10);
    return view('admin.visites.allList', compact('visites', 'pendingVisits'));
 }

 public function adminConfirm(Visite $visite, Request $request)
{
    $visite->statut = 'confirmée';
    
    // Si un motif est fourni (lors d'un changement de date)
    if ($request->has('motif')) {
        $visite->motif = $request->motif;
    }
    
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

   public function adminCancel(Visite $visite, Request $request)
{
    $visite->statut = 'annulée';
    $visite->motif = $request->motif; // Sauvegarder le motif
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
  public function ownerConfirm(Visite $visite)
    {
        $visite->statut = 'confirmée';
        $visite->save();

        // Envoyer un email de confirmation
        Mail::to($visite->email)->send(new ConfirmVisite($visite, $visite->bien));

        return response()->json(['success' => true]);
    }


    public function ownerMarkAsDone(Visite $visite)
    {
        $visite->statut = 'effectuée';
        $visite->save();

        // Envoyer un email d'effectuation 
        Mail::to($visite->email)->send(new DoneVisite($visite, $visite->bien));

        return response()->json(['success' => true]);
    }

    public function ownerCancel(Visite $visite, Request $request)
    {
        $visite->statut = 'annulée';
        $visite->motif = $request->motif; // Sauvegarder le motif
        $visite->save();

        // Envoyer un email d'annulation 
        Mail::to($visite->email)->send(new CancelVisite($visite, $visite->bien));

        return response()->json(['success' => true]);
    }

    public function ownerShow(Visite $visite)
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
