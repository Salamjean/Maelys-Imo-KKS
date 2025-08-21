<?php

namespace App\Http\Controllers;

use App\Mail\CancelVisite;
use App\Mail\ConfirmVisite;
use App\Mail\DoneVisite;
use App\Mail\VisiteConfirmation;
use App\Models\Bien;
use App\Models\Visite;
use Twilio\Rest\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Twilio\Exceptions\TwilioException;

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
            function ($attribute, $value, $fail) {
                try {
                    $formatted = $this->formatIvorianNumberForTwilio($value);
                    if (!preg_match('/^\+225[1-9]\d{7}$/', $formatted)) {
                        throw new \Exception('Format invalide');
                    }
                } catch (\Exception $e) {
                    $fail('Le numéro doit être un mobile ivoirien valide (ex: 07 98 27 89 81)');
                }
            }
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

    // Envoyer un SMS
    // try {
    //     $phoneNumber = $this->formatIvorianNumberForTwilio($validated['telephone']);
        
    //     $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
        
    //     $httpClient = new \Twilio\Http\CurlClient([
    //         CURLOPT_CAINFO => storage_path('certs/cacert.pem'),
    //         CURLOPT_SSL_VERIFYPEER => true,
    //         CURLOPT_SSL_VERIFYHOST => 2,
    //     ]);
    //     $twilio->setHttpClient($httpClient);

    //     $message = $twilio->messages->create(
    //         $phoneNumber,
    //         [
    //             'from' => env('TWILIO_PHONE_NUMBER'),
    //             'body' => "Bonjour {$validated['nom']}, votre visite pour {$bien->type} est confirmée."
    //         ]
    //     );

    //     Log::info("SMS envoyé à $phoneNumber");

    // } catch (TwilioException $e) {
    //     Log::error("Erreur Twilio: " . $e->getMessage());
    // }

    return redirect()->route('login')->with('success', 'Votre demande de visite a été enregistrée avec succès. Nous vous contacterons pour confirmation.');
}

/**
 * Formatage robuste pour les numéros ivoiriens
 */
private function formatIvorianNumberForTwilio(string $phone): string
{
    // Nettoyage complet
    $cleaned = preg_replace('/[^0-9+]/', '', $phone);
    
    // Si déjà au format +225...
    if (str_starts_with($cleaned, '+225') && strlen($cleaned) === 12) {
        return $cleaned;
    }
    
    // Suppression du + ou 00
    $cleaned = ltrim($cleaned, '+');
    $cleaned = preg_replace('/^00/', '', $cleaned);
    
    // Extraction des derniers 8 chiffres
    $baseNumber = substr($cleaned, -8);
    
    // Vérification du numéro mobile ivoirien
    if (!preg_match('/^[1-9]\d{7}$/', $baseNumber)) {
        throw new \Exception('Numéro mobile ivoirien invalide');
    }
    
    return '+225' . $baseNumber;
}

   public function confirm(Visite $visite)
{
    // Mettre à jour le statut
    $visite->statut = 'confirmée';
    $visite->save();

    // Envoyer un email de confirmation
    Mail::to($visite->email)->send(new ConfirmVisite($visite, $visite->bien));

    // Envoyer un SMS de confirmation
    try {
        $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
        
        // Configurer SSL (si nécessaire)
        $httpClient = new \Twilio\Http\CurlClient([
            CURLOPT_CAINFO => storage_path('certs/cacert.pem'),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $twilio->setHttpClient($httpClient);

        // Formater le numéro
        $phoneNumber = $this->formatIvorianNumberForTwilio($visite->telephone);

        // Envoyer le SMS
        $message = $twilio->messages->create(
            $phoneNumber,
            [
                'from' => env('TWILIO_PHONE_NUMBER'), // ou un Alphanumeric Sender ID
                'body' => "Bonjour {$visite->nom}, votre visite du {$visite->date_visite} à {$visite->heure_visite} est confirmée. Bien: {$visite->bien->type}"
            ]
        );

        // Logger le succès
        Log::channel('sms')->info("SMS de confirmation envoyé", [
            'visite_id' => $visite->id,
            'to' => $phoneNumber,
            'sid' => $message->sid
        ]);

    } catch (TwilioException $e) {
        Log::channel('sms')->error("Erreur SMS confirmation", [
            'visite_id' => $visite->id,
            'error' => $e->getMessage(),
            'code' => $e->getCode()
        ]);
    }

    return response()->json(['success' => true]);
}


    public function markAsDone(Visite $visite)
{
    // Mettre à jour le statut
    $visite->statut = 'effectuée';
    $visite->save();

    // Envoyer un email
    Mail::to($visite->email)->send(new DoneVisite($visite, $visite->bien));

    // Envoyer un SMS de notification
    try {
        $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
        
        // Configuration SSL (obligatoire en production)
        $httpClient = new \Twilio\Http\CurlClient([
            CURLOPT_CAINFO => storage_path('certs/cacert.pem'),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $twilio->setHttpClient($httpClient);

        // Formater le numéro (méthode réutilisée depuis votre code)
        $phoneNumber = $this->formatIvorianNumberForTwilio($visite->telephone);

        // Contenu du SMS plus complet
        $smsContent = "Merci {$visite->nom} d'avoir visité notre bien \"{$visite->bien->type}\". "
                   . "Votre avis nous intéresse ! Répondez à ce SMS pour nous faire part de vos impressions.";

        $message = $twilio->messages->create(
            $phoneNumber,
            [
                'from' => env('TWILIO_PHONE_NUMBER'), // Ou 'MONAGENCE' pour Alphanumeric Sender ID
                'body' => $smsContent,
            ]
        );

        // Log de succès structuré
        Log::channel('sms')->info('SMS visite effectuée envoyé', [
            'visite_id' => $visite->id,
            'numero' => $phoneNumber,
            'message_sid' => $message->sid,
            'timestamp' => now()->toDateTimeString()
        ]);

    } catch (TwilioException $e) {
        Log::channel('sms')->error('Erreur Twilio', [
            'visite_id' => $visite->id,
            'error' => $e->getMessage(),
            'code' => $e->getCode(),
            'stack' => $e->getTraceAsString()
        ]);
    }

    return response()->json([
        'success' => true,
        'sms_sent' => isset($message) ? true : false
    ]);
}

   public function cancel(Visite $visite, Request $request)
{
    $request->validate([
        'motif' => 'required|string|max:255'
    ]);

    // Mise à jour de la visite
    $visite->statut = 'annulée';
    $visite->motif = $request->motif;
    $visite->save();

    // Envoyer un email
    Mail::to($visite->email)->send(new CancelVisite($visite, $visite->bien));

    // Envoyer un SMS d'annulation
    try {
        $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
        
        // Configuration SSL
        $httpClient = new \Twilio\Http\CurlClient([
            CURLOPT_CAINFO => storage_path('certs/cacert.pem'),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $twilio->setHttpClient($httpClient);

        // Formater le numéro
        $phoneNumber = $this->formatIvorianNumberForTwilio($visite->telephone);

        // Message SMS clair avec motif
        $smsContent = "Monsieur/Madame {$visite->nom}, votre visite du {$visite->date_visite} "
                    . "a été annulée. Motif: {$request->motif}. "
                    . "Nous restons à disposition pour reprogrammer. "
                    . "Contact: " . config('app.contact_phone');

        $message = $twilio->messages->create(
            $phoneNumber,
            [
                'from' => env('TWILIO_PHONE_NUMBER'), // Ou votre Alphanumeric Sender ID
                'body' => $smsContent,
            ]
        );

        // Log structuré
        Log::channel('sms')->info('SMS annulation envoyé', [
            'visite_id' => $visite->id,
            'to' => $phoneNumber,
            'message_sid' => $message->sid,
            'motif' => $request->motif
        ]);

    } catch (TwilioException $e) {
        Log::channel('sms')->error('Échec envoi SMS annulation', [
            'visite_id' => $visite->id,
            'error' => $e->getMessage(),
            'code' => $e->getCode()
        ]);
    }

    return response()->json([
        'success' => true,
        'sms_sent' => isset($message) ? true : false,
        'motif' => $request->motif
    ]);
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
    // Envoyer un SMS de confirmation
    try {
        $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
        
        // Configurer SSL (si nécessaire)
        $httpClient = new \Twilio\Http\CurlClient([
            CURLOPT_CAINFO => storage_path('certs/cacert.pem'),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $twilio->setHttpClient($httpClient);

        // Formater le numéro
        $phoneNumber = $this->formatIvorianNumberForTwilio($visite->telephone);

        // Envoyer le SMS
        $message = $twilio->messages->create(
            $phoneNumber,
            [
                'from' => env('TWILIO_PHONE_NUMBER'), // ou un Alphanumeric Sender ID
                'body' => "Bonjour {$visite->nom}, votre visite du {$visite->date_visite} à {$visite->heure_visite} est confirmée. Bien: {$visite->bien->type}"
            ]
        );

        // Logger le succès
        Log::channel('sms')->info("SMS de confirmation envoyé", [
            'visite_id' => $visite->id,
            'to' => $phoneNumber,
            'sid' => $message->sid
        ]);

    } catch (TwilioException $e) {
        Log::channel('sms')->error("Erreur SMS confirmation", [
            'visite_id' => $visite->id,
            'error' => $e->getMessage(),
            'code' => $e->getCode()
        ]);
    }

    return response()->json(['success' => true]);
}


    public function adminMarkAsDone(Visite $visite)
    {
        $visite->statut = 'effectuée';
        $visite->save();

        // Envoyer un email d'effectuation 
        Mail::to($visite->email)->send(new DoneVisite($visite, $visite->bien));

        // Envoyer un SMS de notification
        try {
            $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
            
            // Configuration SSL (obligatoire en production)
            $httpClient = new \Twilio\Http\CurlClient([
                CURLOPT_CAINFO => storage_path('certs/cacert.pem'),
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);
            $twilio->setHttpClient($httpClient);

            // Formater le numéro (méthode réutilisée depuis votre code)
            $phoneNumber = $this->formatIvorianNumberForTwilio($visite->telephone);

            // Contenu du SMS plus complet
            $smsContent = "Merci {$visite->nom} d'avoir visité notre bien \"{$visite->bien->type}\". "
                    . "Votre avis nous intéresse ! Répondez à ce SMS pour nous faire part de vos impressions.";

            $message = $twilio->messages->create(
                $phoneNumber,
                [
                    'from' => env('TWILIO_PHONE_NUMBER'), // Ou 'MONAGENCE' pour Alphanumeric Sender ID
                    'body' => $smsContent,
                ]
            );

            // Log de succès structuré
            Log::channel('sms')->info('SMS visite effectuée envoyé', [
                'visite_id' => $visite->id,
                'numero' => $phoneNumber,
                'message_sid' => $message->sid,
                'timestamp' => now()->toDateTimeString()
            ]);

        } catch (TwilioException $e) {
            Log::channel('sms')->error('Erreur Twilio', [
                'visite_id' => $visite->id,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'stack' => $e->getTraceAsString()
            ]);
        }

        return response()->json([
            'success' => true,
            'sms_sent' => isset($message) ? true : false
        ]);

        return response()->json(['success' => true]);
    }

   public function adminCancel(Visite $visite, Request $request)
{
    $visite->statut = 'annulée';
    $visite->motif = $request->motif; // Sauvegarder le motif
    $visite->save();

    // Envoyer un email d'annulation 
    Mail::to($visite->email)->send(new CancelVisite($visite, $visite->bien));

        // Envoyer un SMS d'annulation
        try {
            $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
            
            // Configuration SSL
            $httpClient = new \Twilio\Http\CurlClient([
                CURLOPT_CAINFO => storage_path('certs/cacert.pem'),
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);
            $twilio->setHttpClient($httpClient);

            // Formater le numéro
            $phoneNumber = $this->formatIvorianNumberForTwilio($visite->telephone);

            // Message SMS clair avec motif
            $smsContent = "Monsieur/Madame {$visite->nom}, votre visite du {$visite->date_visite} "
                        . "a été annulée. Motif: {$request->motif}. "
                        . "Nous restons à disposition pour reprogrammer. "
                        . "Contact: " . config('app.contact_phone');

            $message = $twilio->messages->create(
                $phoneNumber,
                [
                    'from' => env('TWILIO_PHONE_NUMBER'), // Ou votre Alphanumeric Sender ID
                    'body' => $smsContent,
                ]
            );

            // Log structuré
            Log::channel('sms')->info('SMS annulation envoyé', [
                'visite_id' => $visite->id,
                'to' => $phoneNumber,
                'message_sid' => $message->sid,
                'motif' => $request->motif
            ]);

        } catch (TwilioException $e) {
            Log::channel('sms')->error('Échec envoi SMS annulation', [
                'visite_id' => $visite->id,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }

        return response()->json([
            'success' => true,
            'sms_sent' => isset($message) ? true : false,
            'motif' => $request->motif
        ]);

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

        //Envoie d'sms 
        try {
            $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
            
            // Configurer SSL (si nécessaire)
            $httpClient = new \Twilio\Http\CurlClient([
                CURLOPT_CAINFO => storage_path('certs/cacert.pem'),
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);
            $twilio->setHttpClient($httpClient);

            // Formater le numéro
            $phoneNumber = $this->formatIvorianNumberForTwilio($visite->telephone);

            // Envoyer le SMS
            $message = $twilio->messages->create(
                $phoneNumber,
                [
                    'from' => env('TWILIO_PHONE_NUMBER'), // ou un Alphanumeric Sender ID
                    'body' => "Bonjour {$visite->nom}, votre visite du {$visite->date_visite} à {$visite->heure_visite} est confirmée. Bien: {$visite->bien->type}"
                ]
            );

            // Logger le succès
            Log::channel('sms')->info("SMS de confirmation envoyé", [
                'visite_id' => $visite->id,
                'to' => $phoneNumber,
                'sid' => $message->sid
            ]);

        } catch (TwilioException $e) {
            Log::channel('sms')->error("Erreur SMS confirmation", [
                'visite_id' => $visite->id,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }

        return response()->json(['success' => true]);
    }


    public function ownerMarkAsDone(Visite $visite)
    {
        $visite->statut = 'effectuée';
        $visite->save();

        // Envoyer un email d'effectuation 
        Mail::to($visite->email)->send(new DoneVisite($visite, $visite->bien));

        // Envoyer un SMS de notification
        try {
            $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
            
            // Configuration SSL (obligatoire en production)
            $httpClient = new \Twilio\Http\CurlClient([
                CURLOPT_CAINFO => storage_path('certs/cacert.pem'),
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);
            $twilio->setHttpClient($httpClient);

            // Formater le numéro (méthode réutilisée depuis votre code)
            $phoneNumber = $this->formatIvorianNumberForTwilio($visite->telephone);

            // Contenu du SMS plus complet
            $smsContent = "Merci {$visite->nom} d'avoir visité notre bien \"{$visite->bien->type}\". "
                    . "Votre avis nous intéresse ! Répondez à ce SMS pour nous faire part de vos impressions.";

            $message = $twilio->messages->create(
                $phoneNumber,
                [
                    'from' => env('TWILIO_PHONE_NUMBER'), // Ou 'MONAGENCE' pour Alphanumeric Sender ID
                    'body' => $smsContent,
                ]
            );

            // Log de succès structuré
            Log::channel('sms')->info('SMS visite effectuée envoyé', [
                'visite_id' => $visite->id,
                'numero' => $phoneNumber,
                'message_sid' => $message->sid,
                'timestamp' => now()->toDateTimeString()
            ]);

        } catch (TwilioException $e) {
            Log::channel('sms')->error('Erreur Twilio', [
                'visite_id' => $visite->id,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'stack' => $e->getTraceAsString()
            ]);
        }

        return response()->json([
            'success' => true,
            'sms_sent' => isset($message) ? true : false
        ]);
    }

    public function ownerCancel(Visite $visite, Request $request)
    {
        $visite->statut = 'annulée';
        $visite->motif = $request->motif; // Sauvegarder le motif
        $visite->save();

        // Envoyer un email d'annulation 
        Mail::to($visite->email)->send(new CancelVisite($visite, $visite->bien));


        // Envoyer un SMS d'annulation
        try {
            $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
            
            // Configuration SSL
            $httpClient = new \Twilio\Http\CurlClient([
                CURLOPT_CAINFO => storage_path('certs/cacert.pem'),
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);
            $twilio->setHttpClient($httpClient);

            // Formater le numéro
            $phoneNumber = $this->formatIvorianNumberForTwilio($visite->telephone);

            // Message SMS clair avec motif
            $smsContent = "Monsieur/Madame {$visite->nom}, votre visite du {$visite->date_visite} "
                        . "a été annulée. Motif: {$request->motif}. "
                        . "Nous restons à disposition pour reprogrammer. "
                        . "Contact: " . config('app.contact_phone');

            $message = $twilio->messages->create(
                $phoneNumber,
                [
                    'from' => env('TWILIO_PHONE_NUMBER'), // Ou votre Alphanumeric Sender ID
                    'body' => $smsContent,
                ]
            );

            // Log structuré
            Log::channel('sms')->info('SMS annulation envoyé', [
                'visite_id' => $visite->id,
                'to' => $phoneNumber,
                'message_sid' => $message->sid,
                'motif' => $request->motif
            ]);

        } catch (TwilioException $e) {
            Log::channel('sms')->error('Échec envoi SMS annulation', [
                'visite_id' => $visite->id,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }

        return response()->json([
            'success' => true,
            'sms_sent' => isset($message) ? true : false,
            'motif' => $request->motif
        ]);

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
