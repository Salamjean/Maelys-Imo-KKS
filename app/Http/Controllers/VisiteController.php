<?php

namespace App\Http\Controllers;

use App\Mail\CancelVisite;
use App\Mail\ConfirmVisite;
use App\Mail\DoneVisite;
use App\Mail\VisiteConfirmation;
use App\Models\Bien;
use App\Models\Visite;
use App\Services\YellikaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                    ->orWhereHas('proprietaire', function ($q) {
                        $q->where('gestion', 'agence'); // 2ème cas: bien avec propriétaire gestion agence
                    });
            })
            ->paginate(10);
        // Demandes de visite en attente
        $pendingVisits = Visite::where('statut', 'en attente')
            ->whereHas('bien', function ($query) {
                $query->whereNull('agence_id');  // Filtrer par l'ID de l'agence
                $query->whereNull('proprietaire_id') // 1er cas: bien sans propriétaire
                    ->orWhereHas('proprietaire', function ($q) {
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

        $visites = Visite::where(function ($query) {
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

        $visites = Visite::where(function ($query) {
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
                    ->orWhereHas('proprietaire', function ($q) {
                        $q->where('gestion', 'agence'); // 2ème cas: bien avec propriétaire gestion agence
                    });
            })
            ->count();
        $adminId = Auth::guard('admin')->user()->id;
        $visites = Visite::whereHas('bien', function ($query) {
            $query->whereNull('agence_id');  // Filtrer par l'ID de l'agence
            $query->whereNull('proprietaire_id') // 1er cas: bien sans propriétaire
                ->orWhereHas('proprietaire', function ($q) {
                    $q->where('gestion', 'agence'); // 2ème cas: bien avec propriétaire gestion agence
                });
        })
            ->paginate(10);
        return view('admin.visites.done', compact('visites', 'pendingVisits'));
    }

    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'bien_id' => 'required|exists:biens,id',
    //         'nom' => 'required|string|max:255',
    //         'email' => 'required|email|max:255',
    //         'telephone' => 'required|string|max:20',
    //         'date_visite' => 'required|date|after_or_equal:today',
    //         'heure_visite' => 'required',
    //         'message' => 'nullable|string|max:50',
    //         'statut' => 'in:en attente,confirmée,effectuée,annulée'
    //     ],[
    //         'bien_id.required' => 'Le bien est obligatoire.',
    //         'bien_id.exists' => 'Le bien sélectionné n\'existe pas.',
    //         'nom.required' => 'Le nom est obligatoire.',
    //         'email.required' => 'L\'email est obligatoire.',
    //         'telephone.required' => 'Le téléphone est obligatoire.',
    //         'date_visite.required' => 'La date de visite est obligatoire.',
    //         'date_visite.after_or_equal' => 'La date de visite doit être aujourd\'hui ou une date future.',
    //         'heure_visite.required' => 'L\'heure de visite est obligatoire.',
    //         'message.max' => 'Le message ne peut pas dépasser 50 caractères.'
    //     ]);

    //     // Créer la visite
    //     $visite = Visite::create($validated);

    //     // Récupérer les infos du bien
    //     $bien = Bien::find($validated['bien_id']);

    //     // Envoyer un email de confirmation (optionnel)
    //     Mail::to($validated['email'])->send(new VisiteConfirmation($visite, $bien));

    //     return redirect()->route('home')->with('success', 'Votre demande de visite a été enregistrée avec succès. Nous vous contacterons pour confirmation.');
    // }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'bien_id' => 'required|exists:biens,id',
            'nom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telephone' => [
                'required',
                'string',
                'max:20',
            ],
            'date_visite' => 'required|date|after_or_equal:today',
            'heure_visite' => 'required',
            'message' => 'nullable|string|max:500',
            'statut' => 'in:en attente,confirmée,effectuée,annulée'
        ]);

        // Créer la visite
        $visite = Visite::create($validated);
        $bien = Bien::find($validated['bien_id']);

        // Envoyer un email de confirmation
        Mail::to($validated['email'])->send(new VisiteConfirmation($visite, $bien));

        // Envoyer un SMS de confirmation de demande de visite
        try {
            $yellika = new YellikaService();
            $sms = "Bonjour {$validated['nom']}, votre demande de visite pour le bien {$bien->type} ({$bien->commune}) le {$validated['date_visite']} a {$validated['heure_visite']} a bien ete enregistree. Nous vous contacterons pour confirmation.";
            $yellika->send($validated['telephone'], $sms);
        } catch (\Exception $e) {
            Log::error('Erreur SMS demande visite', ['error' => $e->getMessage()]);
        }

        return redirect('/')->with('success', 'Votre demande de visite a été enregistrée avec succès. Nous vous contacterons pour confirmation.');
    }

    /**
     * Formatage robuste pour les numéros ivoiriens
     */
    /**
     * Formatage du numéro (conservé pour compatibilité)
     * @deprecated Utiliser YellikaService::normalizePhone() directement
     */
    private function formatIvorianNumberForTwilio(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);
        if (str_starts_with($cleaned, '+225')) return $cleaned;
        $cleaned = ltrim($cleaned, '+');
        $cleaned = preg_replace('/^00/', '', $cleaned);
        if (str_starts_with($cleaned, '225')) return '+' . $cleaned;
        if (str_starts_with($cleaned, '0')) return '+225' . $cleaned;
        return '+225' . $cleaned;
    }

    public function confirm(Visite $visite)
    {
        $visite->statut = 'confirmée';
        $visite->save();

        Mail::to($visite->email)->send(new ConfirmVisite($visite, $visite->bien));

        try {
            $yellika = new YellikaService();
            $sms = "Bonjour {$visite->nom}, votre visite du {$visite->date_visite} a {$visite->heure_visite} est confirmee. Bien: {$visite->bien->type}";
            $yellika->send($visite->telephone, $sms);
        } catch (\Exception $e) {
            Log::error('Erreur SMS confirmation visite', ['visite_id' => $visite->id, 'error' => $e->getMessage()]);
        }

        return response()->json(['success' => true]);
    }


    public function markAsDone(Visite $visite)
    {
        $visite->statut = 'effectuée';
        $visite->save();

        Mail::to($visite->email)->send(new DoneVisite($visite, $visite->bien));

        try {
            $yellika = new YellikaService();
            $sms = "Merci {$visite->nom} d'avoir visite notre bien \"{$visite->bien->type}\". Votre avis nous interesse !";
            $yellika->send($visite->telephone, $sms);
        } catch (\Exception $e) {
            Log::error('Erreur SMS visite effectuée', ['visite_id' => $visite->id, 'error' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'sms_sent' => true]);
    }

    public function cancel(Visite $visite, Request $request)
    {
        $request->validate(['motif' => 'required|string|max:255']);

        $visite->statut = 'annulée';
        $visite->motif = $request->motif;
        $visite->save();

        Mail::to($visite->email)->send(new CancelVisite($visite, $visite->bien));

        try {
            $yellika = new YellikaService();
            $sms = "Bonjour {$visite->nom}, votre visite du {$visite->date_visite} a ete annulee. Motif: {$request->motif}. Nous restons disponibles pour reprogrammer.";
            $yellika->send($visite->telephone, $sms);
        } catch (\Exception $e) {
            Log::error('Erreur SMS annulation visite', ['visite_id' => $visite->id, 'error' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'sms_sent' => true, 'motif' => $request->motif]);
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
    public function allVisit()
    {
        // Demandes de visite en attente
        $pendingVisits = Visite::where('statut', 'en attente')
            ->whereHas('bien', function ($query) {
                $query->whereNull('agence_id');  // Filtrer par l'ID de l'agence
                $query->whereNull('proprietaire_id') // 1er cas: bien sans propriétaire
                    ->orWhereHas('proprietaire', function ($q) {
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
        if ($request->has('motif')) {
            $visite->motif = $request->motif;
        }
        $visite->save();

        Mail::to($visite->email)->send(new ConfirmVisite($visite, $visite->bien));

        try {
            $yellika = new YellikaService();
            $sms = "Bonjour {$visite->nom}, votre visite du {$visite->date_visite} a {$visite->heure_visite} est confirmee. Bien: {$visite->bien->type}";
            $yellika->send($visite->telephone, $sms);
        } catch (\Exception $e) {
            Log::error('Erreur SMS adminConfirm', ['visite_id' => $visite->id, 'error' => $e->getMessage()]);
        }

        return response()->json(['success' => true]);
    }


    public function adminMarkAsDone(Visite $visite)
    {
        $visite->statut = 'effectuée';
        $visite->save();

        Mail::to($visite->email)->send(new DoneVisite($visite, $visite->bien));

        try {
            $yellika = new YellikaService();
            $sms = "Merci {$visite->nom} d'avoir visite notre bien \"{$visite->bien->type}\". Votre avis nous interesse !";
            $yellika->send($visite->telephone, $sms);
        } catch (\Exception $e) {
            Log::error('Erreur SMS adminMarkAsDone', ['visite_id' => $visite->id, 'error' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'sms_sent' => true]);
    }

    public function adminCancel(Visite $visite, Request $request)
    {
        $visite->statut = 'annulée';
        $visite->motif = $request->motif;
        $visite->save();

        Mail::to($visite->email)->send(new CancelVisite($visite, $visite->bien));

        try {
            $yellika = new YellikaService();
            $sms = "Bonjour {$visite->nom}, votre visite du {$visite->date_visite} a ete annulee. Motif: {$request->motif}. Nous restons disponibles pour reprogrammer.";
            $yellika->send($visite->telephone, $sms);
        } catch (\Exception $e) {
            Log::error('Erreur SMS adminCancel', ['visite_id' => $visite->id, 'error' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'sms_sent' => true, 'motif' => $request->motif]);
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

        Mail::to($visite->email)->send(new ConfirmVisite($visite, $visite->bien));

        try {
            $yellika = new YellikaService();
            $sms = "Bonjour {$visite->nom}, votre visite du {$visite->date_visite} a {$visite->heure_visite} est confirmee. Bien: {$visite->bien->type}";
            $yellika->send($visite->telephone, $sms);
        } catch (\Exception $e) {
            Log::error('Erreur SMS ownerConfirm', ['visite_id' => $visite->id, 'error' => $e->getMessage()]);
        }

        return response()->json(['success' => true]);
    }


    public function ownerMarkAsDone(Visite $visite)
    {
        $visite->statut = 'effectuée';
        $visite->save();

        Mail::to($visite->email)->send(new DoneVisite($visite, $visite->bien));

        try {
            $yellika = new YellikaService();
            $sms = "Merci {$visite->nom} d'avoir visite notre bien \"{$visite->bien->type}\". Votre avis nous interesse !";
            $yellika->send($visite->telephone, $sms);
        } catch (\Exception $e) {
            Log::error('Erreur SMS ownerMarkAsDone', ['visite_id' => $visite->id, 'error' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'sms_sent' => true]);
    }

    public function ownerCancel(Visite $visite, Request $request)
    {
        $visite->statut = 'annulée';
        $visite->motif = $request->motif;
        $visite->save();

        Mail::to($visite->email)->send(new CancelVisite($visite, $visite->bien));

        try {
            $yellika = new YellikaService();
            $sms = "Bonjour {$visite->nom}, votre visite du {$visite->date_visite} a ete annulee. Motif: {$request->motif}. Nous restons disponibles pour reprogrammer.";
            $yellika->send($visite->telephone, $sms);
        } catch (\Exception $e) {
            Log::error('Erreur SMS ownerCancel', ['visite_id' => $visite->id, 'error' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'sms_sent' => true, 'motif' => $request->motif]);
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
