<?php

namespace App\Http\Controllers\Proprietaire;
use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\Locataire;
use App\Models\Paiement;
use App\Models\ResetCodePasswordLocataire;
use App\Models\Visite;
use App\Notifications\SendEmailToLocataireAfterRegistrationNotification;
use Carbon\Carbon;
use PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Twilio\Rest\Client;
use Illuminate\Support\Str;
use Twilio\Exceptions\TwilioException;

class LocataireOwnerController extends Controller
{

    public function index()
    {
        $ownerId = Auth::guard('owner')->user()->code_id;
     // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')->where('statut', '!=', 'effectuée')
                        ->where('statut', '!=', 'annulée')
                        ->whereHas('bien', function ($query) use ($ownerId) {
                             $query->where('proprietaire_id', $ownerId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        // Récupération des locataires avec les relations nécessaires
        $locataires = Locataire::with(['bien', 'paiements' => function($query) {
            $query->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
            }])
            ->where('status', '!=', 'Pas sérieux')
            ->where('proprietaire_id',$ownerId)
            ->whereNotNull('bien_id')
            ->paginate(6);

        // Ajout d'une propriété à chaque locataire pour déterminer si le bouton doit être affiché
        $locataires->getCollection()->transform(function($locataire) {
            $today = now()->format('d');
            $currentMonthPaid = $locataire->paiements->isNotEmpty();
            $locataire->show_reminder_button = ($locataire->bien->date_fixe ?? '10' == $today ) && !$currentMonthPaid ;
            return $locataire;
        });

        return view('proprietaire.locataire.index', compact('locataires', 'pendingVisits'));
    }
    public function create()
    {
        // Récupérer les biens disponibles de l'agence
          $ownerId = Auth::guard('owner')->user()->code_id;
     // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')->where('statut', '!=', 'effectuée')
                        ->where('statut', '!=', 'annulée')
                        ->whereHas('bien', function ($query) use ($ownerId) {
                             $query->where('proprietaire_id', $ownerId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        $biens = Bien::where('proprietaire_id', $ownerId)
                    ->where('status', 'Disponible')
                    ->get();
        
        return view('proprietaire.locataire.create', compact('biens', 'pendingVisits'));
    }
    
    public function store(Request $request)
    {
        // Vérification des doublons potentiels
        $existingLocataires = Locataire::where(function($query) use ($request) {
            $query->where('name', 'like', $request->name)
                  ->orWhere('prenom', 'like', $request->prenom)
                  ->orWhere('email', $request->email)
                  ->orWhere('contact', $request->contact);
        })->get();

        $isDuplicate = false;
        foreach ($existingLocataires as $locataire) {
            $matchCount = 0;
            if (strtolower($locataire->name) === strtolower($request->name)) $matchCount++;
            if (strtolower($locataire->prenom) === strtolower($request->prenom)) $matchCount++;
            if ($locataire->email === $request->email) $matchCount++;
            if ($locataire->contact === $request->contact) $matchCount++;
            
            if ($matchCount >= 2) {
                $isDuplicate = true;
                break;
            }
        }

        if ($isDuplicate) {
            return back()->withErrors([
                'duplicate' => 'Un locataire avec des informations similaires existe déjà. Veuillez vérifier dans la liste des locataires ou dans locataire pas sérieux.'
            ])->withInput();
        }

        // Validation des données
        $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:locataires,email',
            'contact' => 'required|string|min:10|unique:locataires,contact',
            'piece' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'adresse' => 'required|string|max:255',
            'profession' => 'required|string|max:255',
            'contrat' => 'required|file|mimes:jpeg,png,jpg,gif,pdf|max:2048',
            'attestation' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image4' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'sometimes|in:Actif,Inactif,Pas sérieux',
            'motif' => 'required_if:status,Inactif,Pas sérieux|nullable|string|max:255',
            'bien_id' => 'required|exists:biens,id',
        ], [
            'name.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email doit être une adresse email valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'contact.required' => 'Le contact est obligatoire.',
            'contact.min' => 'Le contact doit avoir au moins 10 chiffres.',
            'contact.unique' => 'Ce numéro de contact est déjà utilisé.',
            'piece.required' => 'La pièce d\'identité est obligatoire.',
            'piece.image' => 'La pièce d\'identité doit être une image.',
            'piece.mimes' => 'La pièce d\'identité doit être de type: jpeg, png, jpg ou gif.',
            'adresse.required' => 'L\'adresse est obligatoire.',
            'profession.required' => 'La profession est obligatoire.',
            'contrat.required' => 'Le contrat est obligatoire.',
            'contrat.file' => 'Le contrat doit être un fichier.',
            'contrat.mimes' => 'Le contrat doit être de type: jpeg, png, jpg, gif ou pdf.',
            'motif.required_if' => 'Le motif est obligatoire lorsque le statut est Inactif ou Pas sérieux.',
            'bien_id.required' => 'Vous devez sélectionner un bien.',
            'duplicate' => 'Un locataire avec des informations similaires existe déjà.'
        ]);

        try {
            // Traitement des images
            $piecePath = null;
            if ($request->hasFile('piece')) {
                $piecePath = $request->file('piece')->store('pieces_identite', 'public');
            }

            $attestationPath = null;
            if ($request->hasFile('attestation')) {
                $attestationPath = $request->file('attestation')->store('attestations', 'public');
            }

            $contratPath = null;
            if ($request->hasFile('contrat')) {
                $contratPath = $request->file('contrat')->store('contrats', 'public');
            }

            $image1Path = null;
            if ($request->hasFile('image1')) {
                $image1Path = $request->file('image1')->store('locataires_images', 'public');
            }

            $image2Path = null;
            if ($request->hasFile('image2')) {
                $image2Path = $request->file('image2')->store('locataires_images', 'public');
            }

            $image3Path = null;
            if ($request->hasFile('image3')) {
                $image3Path = $request->file('image3')->store('locataires_images', 'public');
            }

            $image4Path = null;
            if ($request->hasFile('image4')) {
                $image4Path = $request->file('image4')->store('locataires_images', 'public');
            }

              // Récupération du bien associé
            $bien = Bien::findOrFail($request->bien_id);
            $avance = $bien->avance;
            $loyer = $bien->prix;
            $datePaiement = now();

            // Création du locataire
            $locataire = new Locataire();
            $locataire->code_id = $this->generateUniqueCodeId();
            $locataire->name = $request->name;
            $locataire->prenom = $request->prenom;
            $locataire->email = $request->email;
            $locataire->password = Hash::make('password');
            $locataire->contact = $request->contact;
            $locataire->piece = $piecePath;
            $locataire->adresse = $request->adresse;
            $locataire->profession = $request->profession;
            $locataire->attestation = $attestationPath;
            $locataire->contrat = $contratPath;
            $locataire->image1 = $image1Path;
            $locataire->image2 = $image2Path;
            $locataire->image3 = $image3Path;
            $locataire->image4 = $image4Path;
            $locataire->proprietaire_id = Auth::guard('owner')->user()->code_id;
            $locataire->status = $request->input('status', 'Actif');
            $locataire->bien_id = $request->bien_id;

            if (in_array($locataire->status, ['Inactif', 'Pas sérieux'])) {
                $locataire->motif = $request->motif;
            }
            
            $locataire->save();

            // Mettre à jour le statut du bien
            $bien = Bien::find($request->bien_id);
            $bien->status = 'Loué';
            $bien->save();

            // Création des paiements pour l'avance
            if ($avance > 0) {
                for ($i = 0; $i < $avance; $i++) {
                    $moisCourant = Carbon::now()->addMonths($i);
                    $moisCouvert = $moisCourant->format('Y-m');
                    
                    Paiement::create([
                        'montant' => $loyer,
                        'date_paiement' => $datePaiement,
                        'reference' => 'AVANCE-' . Str::random(8),
                        'mois_couvert' => $moisCouvert,
                        'methode_paiement' => 'Espèces',
                        'statut' => 'payé',
                        'locataire_id' => $locataire->id,
                        'bien_id' => $bien->id,
                        'proof_path' => $contratPath // On utilise le contrat comme preuve de paiement
                    ]);
                }
            }

            // Envoi de l'e-mail de vérification
            $agence = Auth::guard('owner')->user();
            ResetCodePasswordLocataire::where('email', $locataire->email)->delete();
            $code = rand(1000, 4000);
            ResetCodePasswordLocataire::create([
                'code' => $code,
                'email' => $locataire->email,
            ]);

            Notification::route('mail', $locataire->email)
                ->notify(new SendEmailToLocataireAfterRegistrationNotification($code, $locataire->email, $agence->name));

            /**********************************************************************
             * INTÉGRATION DU SYSTÈME D'ENVOI DE SMS AVEC TWILIO (NOUVEAU CODE)
             **********************************************************************/
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
                    $phoneNumber = $this->formatPhoneNumberForSms($locataire->contact);

                    // Construire le message SMS avec code et lien
                    $validationUrl = url('/validate-locataire-account/' . $locataire->email);
                    $smsContent = "Bonjour {$locataire->name} {$locataire->prenom},\n\n"
                                . "Votre code de validation: {$code}\n\n"
                                . "Validez votre compte ici: {$validationUrl}\n\n"
                                . "Propriétaire: {$agence->name} {$agence->prenom}";

                    $message = $twilio->messages->create(
                        $phoneNumber,
                        [
                            'from' => env('TWILIO_PHONE_NUMBER'),
                            'body' => $smsContent,
                        ]
                    );

                    Log::channel('sms')->info('SMS validation envoyé', [
                        'locataire_id' => $locataire->id,
                        'to' => $phoneNumber,
                        'message_sid' => $message->sid
                    ]);

                } catch (TwilioException $e) {
                    Log::channel('sms')->error('Erreur SMS validation', [
                        'locataire_id' => $locataire->id,
                        'error' => $e->getMessage()
                    ]);
            }
            /**********************************************************************
             * FIN DE L'INTÉGRATION SMS
             **********************************************************************/
                
            return redirect()->route('locataire.index.owner')->with('success', 'Locataire créé avec succès!');

        } catch (\Exception $e) {
            Log::error('Error creating locataire: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Méthode pour formater le numéro de téléphone (à ajouter à votre contrôleur)
     */
    private function formatPhoneNumberForSms(string $phone): string
    {
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
        
        // Vérification du numéro
        if (!preg_match('/^[0-9]{8,15}$/', $baseNumber)) {
            throw new \Exception('Numéro de téléphone invalide');
        }
        
        return '+225' . $baseNumber;
    }

         private function generateUniqueCodeId()
    {
        do {
            $code = 'MA' . str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (Locataire::where('code_id', $code)->exists());

        return $code;
    }

     public function edit($id)
    {
        $locataire = Locataire::findOrFail($id);
        // Récupérer les biens disponibles du proprietaire
          $ownerId = Auth::guard('owner')->user()->code_id;
     // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')->where('statut', '!=', 'effectuée')
                        ->where('statut', '!=', 'annulée')
                        ->whereHas('bien', function ($query) use ($ownerId) {
                             $query->where('proprietaire_id', $ownerId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        $biens = Bien::where('status', '!=', 'Loué')
                ->where('proprietaire_id', $ownerId)
                ->orWhere('id', $locataire->bien_id)
                ->get();
        
        return view('proprietaire.locataire.edit', compact('locataire', 'biens', 'pendingVisits'));
    }

        public function update(Request $request, $id)
{
    $locataire = Locataire::findOrFail($id);

    // Vérification des doublons potentiels (en excluant le locataire actuel)
    $existingLocataires = Locataire::where('id', '!=', $id)
        ->where(function($query) use ($request) {
            $query->where('name', 'like', $request->name)
                  ->orWhere('prenom', 'like', $request->prenom)
                  ->orWhere('email', $request->email)
                  ->orWhere('contact', $request->contact);
        })->get();

    $isDuplicate = false;
    foreach ($existingLocataires as $existing) {
        $matchCount = 0;
        if (strtolower($existing->name) === strtolower($request->name)) $matchCount++;
        if (strtolower($existing->prenom) === strtolower($request->prenom)) $matchCount++;
        if ($existing->email === $request->email) $matchCount++;
        if ($existing->contact === $request->contact) $matchCount++;
        
        if ($matchCount >= 2) {
            $isDuplicate = true;
            break;
        }
    }

    if ($isDuplicate) {
        return back()->withErrors([
            'duplicate' => 'Un locataire avec des informations similaires existe déjà. Veuillez vérifier dans la liste des locataires.'
        ])->withInput();
    }

    // Validation des données
    $request->validate([
        'name' => 'required|string|max:255',
        'prenom' => 'required|string|max:255',
        'email' => 'required|email|unique:locataires,email,'.$locataire->id,
        'contact' => 'required|string|min:10|unique:locataires,contact,'.$locataire->id,
        'piece' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'adresse' => 'required|string|max:255',
        'profession' => 'required|string|max:255',
        'attestation' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'image1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'image2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'image3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'image4' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'contrat' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:2048',
        'status' => 'sometimes|in:Actif,Inactif,Pas sérieux',
        'motif' => 'required_if:status,Inactif,Pas sérieux|nullable|string|max:255',
        'bien_id' => 'required|exists:biens,id',
    ]);

    try {
        // Mise à jour des informations de base
        $locataire->name = $request->name;
        $locataire->prenom = $request->prenom;
        $locataire->email = $request->email;
        $locataire->contact = $request->contact;
        $locataire->adresse = $request->adresse;
        $locataire->profession = $request->profession;
        $locataire->status = $request->input('status', 'Actif');

        if (in_array($locataire->status, ['Inactif', 'Pas sérieux'])) {
            $locataire->motif = $request->motif;
        } else {
            $locataire->motif = null;
        }

        // Gestion des fichiers
        $fileFields = [
            'piece' => 'pieces_identite',
            'attestation' => 'attestations',
            'image1' => 'locataires_images',
            'image2' => 'locataires_images',
            'image3' => 'locataires_images',
            'image4' => 'locataires_images',
            'contrat' => 'contrats'
        ];

        foreach ($fileFields as $field => $folder) {
            if ($request->hasFile($field)) {
                // Supprimer l'ancien fichier si existe
                if ($locataire->$field) {
                    Storage::disk('public')->delete($locataire->$field);
                }
                $filePath = $request->file($field)->store($folder, 'public');
                $locataire->$field = $filePath;
            }
        }

        // Mise à jour du bien si changé
        if ($locataire->bien_id != $request->bien_id) {
            // Libérer l'ancien bien
            $ancienBien = Bien::find($locataire->bien_id);
            if ($ancienBien) {
                $ancienBien->status = 'Disponible';
                $ancienBien->save();
            }

            // Attribuer le nouveau bien
            $locataire->bien_id = $request->bien_id;
            $nouveauBien = Bien::find($request->bien_id);
            $nouveauBien->status = 'Loué';
            $nouveauBien->save();
        }

        $locataire->save();

        return redirect()->route('locataire.index.owner')->with('success', 'Locataire mis à jour avec succès!');

    } catch (\Exception $e) {
        Log::error('Error updating locataire: ' . $e->getMessage());
        return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
    }
}

    public function indexSerieux(){
          $ownerId = Auth::guard('owner')->user()->code_id;
     // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')->where('statut', '!=', 'effectuée')
                        ->where('statut', '!=', 'annulée')
                        ->whereHas('bien', function ($query) use ($ownerId) {
                             $query->where('proprietaire_id', $ownerId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        // Récupération de tous les locataires
        $locataires = Locataire::where('status', 'Pas sérieux')
                    ->paginate(6);
        return view('proprietaire.locataire.indexSerieux',compact('locataires', 'pendingVisits'));
    }

    public function move(){
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
        // Récupération de tous les locataires
        $locataires = Locataire::where('status','Inactif')
                    ->whereNull('bien_id')
                    ->paginate(6);

        
         // Ajout d'une propriété à chaque locataire pour déterminer si le bouton doit être affiché
        $locataires->getCollection()->transform(function($locataire) {
            $today = now()->format('d');
            $currentMonthPaid = $locataire->paiements->isNotEmpty();
            $locataire->show_reminder_button = ($locataire->bien->date_fixe ?? '10' == $today) && !$currentMonthPaid;
            return $locataire;
        });
        return view('proprietaire.locataire.move', compact('locataires', 'pendingVisits'));
    }

     public function updateStatus(Request $request, Locataire $locataire)
    {
        $request->validate([
            'status' => 'required|in:Actif,Inactif,Pas sérieux',
            'motif' => 'required_if:status,Inactif,Pas sérieux|nullable|string|max:255'
        ]);

        try {
            // Sauvegarder l'ancien bien_id avant de le modifier
            $ancienBienId = $locataire->bien_id;

            $locataire->status = $request->status;
            $locataire->motif = in_array($request->status, ['Inactif', 'Pas sérieux']) ? $request->motif : null;
            
            // Si le statut est "Pas sérieux", on libère le bien
            if ($request->status === 'Pas sérieux') {
                $locataire->bien_id = null;
            }
            
            $locataire->save();

            // Mettre à jour le statut du bien si nécessaire
            if ($ancienBienId && $request->status === 'Pas sérieux') {
                $bien = Bien::find($ancienBienId);
                $bien->status = 'Disponible';
                $bien->save();
            }

            return redirect()->back()->with('success', 'Statut du locataire mis à jour avec succès!');
        } catch (\Exception $e) {
            Log::error('Error updating locataire status: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Une erreur est survenue lors de la mise à jour du statut']);
        }
    }
}
