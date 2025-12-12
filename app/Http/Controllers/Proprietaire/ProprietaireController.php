<?php

namespace App\Http\Controllers\Proprietaire;
use App\Http\Controllers\Controller;
use App\Models\Abonnement;
use App\Models\Bien;
use App\Models\Paiement;
use App\Models\Proprietaire;
use App\Models\HistoriqueBien;
use App\Models\ResetCodePasswordProprietaire;
use App\Models\Reversement;
use App\Models\Visite;
use App\Notifications\SendEmailToOwnerAfterRegistrationNotification;
use Exception;
use Twilio\Rest\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\Locataire;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Twilio\Exceptions\TwilioException;

class ProprietaireController extends Controller
{
        private function calculerSoldeDisponible($proprietaireId)
        {
            $totalPaiements = Paiement::where('methode_paiement', 'Mobile Money')
                ->whereHas('bien', function($query) use ($proprietaireId) {
                    $query->where('proprietaire_id', $proprietaireId);
                })
                ->where('statut', 'payé')
                ->sum('montant');
            
            $totalReversements = Reversement::where('proprietaire_id', $proprietaireId)
                ->sum('montant');
            
            return $totalPaiements - $totalReversements;
        }
   public function dashboard()
    {
        if (!auth('owner')->check()) {
            return redirect()->route('owner.login');
       }
        $ownerId = Auth::guard('owner')->user()->code_id;
     // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')->where('statut', '!=', 'effectuée')
                        ->where('statut', '!=', 'annulée')
                        ->whereHas('bien', function ($query) use ($ownerId) {
                             $query->where('proprietaire_id', $ownerId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        // Vérifier si l'utilisateur est connecté en tant que propriétaire
        $proprietaire = Auth::guard('owner')->user();
        $proprietaireId = $proprietaire->code_id;
        
        // Vérification de l'abonnement
        $abonnementActif = Abonnement::where('proprietaire_id', $proprietaireId)
                            ->where('statut', 'actif')
                            ->where('date_fin', '>=', now())
                            ->exists();
        
        // Statistiques du dashboard
        $totalBiens = Bien::where('proprietaire_id', $proprietaireId)->count();
        $cumulLoyers = Bien::where('proprietaire_id', $proprietaireId)->sum('prix');
        
        $biensDisponibles = Bien::where('proprietaire_id', $proprietaireId)
                            ->where('status', 'Disponible')
                            ->count();
        
        $biensOccupes = Bien::where('proprietaire_id', $proprietaireId)
                        ->where('status', 'Loué')
                        ->count();
        
        $pourcentageDisponibles = $totalBiens > 0 ? round(($biensDisponibles / $totalBiens) * 100) : 0;
        $pourcentageOccupes = $totalBiens > 0 ? round(($biensOccupes / $totalBiens) * 100) : 0;

        // Récupérer les 5 derniers biens ajoutés
        $derniersBiens = Bien::where('proprietaire_id', $proprietaireId)
                            ->latest()
                            ->take(5)
                            ->get();

        $soldeDisponible = $this->calculerSoldeDisponible($proprietaireId);

        return view('proprietaire.dashboard', compact(
            'totalBiens',
            'cumulLoyers',
            'biensDisponibles',
            'biensOccupes',
            'pourcentageDisponibles',
            'pourcentageOccupes',
            'derniersBiens',
            'soldeDisponible',
            'abonnementActif',
            'proprietaire',
            'pendingVisits'
        ));
    }
    public function index(){
         $agenceId = Auth::guard('agence')->user()->code_id;
        // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')
                        ->whereHas('bien', function ($query) use ($agenceId) {
                            $query->where('agence_id', $agenceId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        $proprietaires = Proprietaire::where('agence_id', $agenceId)->paginate(6);
        return view('agence.proprietaire.index',compact('proprietaires', 'pendingVisits'));
    }

    public function create()
    {
         $agenceId = Auth::guard('agence')->user()->code_id;
        // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')
                        ->whereHas('bien', function ($query) use ($agenceId) {
                            $query->where('agence_id', $agenceId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        return view('agence.proprietaire.create', compact('pendingVisits'));
    }

    public function store(Request $request)
    {
        // Validation des données
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:proprietaires,email',
            'contact' => 'required|string|min:10',
            'commune' => 'required|string|max:255',
            'pourcentage' => 'required|integer|between:1,15', // Corrigé: requis et entier
            'choix_paiement' => 'required|in:Virement Bancaire,Chèques',
            // Le RIB est requis SEULEMENT si virement bancaire
            'rib' => 'required_if:choix_paiement,Virement Bancaire|nullable|string|max:255',
            // On accepte PDF et Images pour le contrat
            'contrat' => 'required|file|mimes:pdf,jpeg,png,jpg|max:5120', // Max 5MB
            'profil_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // On accepte PDF et Images pour la CNI
            'cni' => 'required|file|mimes:pdf,jpeg,png,jpg|max:5120',
        ],[
            'name.required' => 'Le nom du proprietaire est obligatoire.',
            'prenom.required' => 'Le prénom du proprietaire est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'contact.required' => 'Le contact est obligatoire.',
            'contact.min' => 'Le contact doit avoir au moins 10 chiffres.',
            'pourcentage.required' => 'Le pourcentage est obligatoire.',
            'rib.required_if' => 'Le RIB est obligatoire pour les virements bancaires.',
            'commune.required' => 'Lieu de residence est obligatoire.',
            'contrat.required' => 'Le contrat est obligatoire.',
            'cni.required' => 'La pièce d\'identité est obligatoire.',
            'contrat.mimes' => 'Le contrat doit être un fichier PDF ou une Image (JPG, PNG).',
            'contrat.max' => 'Le fichier du contrat est trop lourd (Max 5Mo).',
            'cni.mimes' => 'La CNI doit être un fichier PDF ou une Image.',
            'cni.max' => 'Le fichier de la CNI est trop lourd (Max 5Mo).',
        ]);

       
   try {
            $agence = Auth::guard('agence')->user();
            $agenceId = $agence->code_id;
            
            // Génération du code PRO unique
            do {
                $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
                $codeId = 'PRO' . $randomNumber;
            } while (Proprietaire::where('code_id', $codeId)->exists());

            // Traitement des fichiers
            $profileImagePath = $request->hasFile('profil_image') 
                ? $request->file('profil_image')->store('profil_images', 'public')
                : null;

            $cniPath = $request->hasFile('cni')
                ? $request->file('cni')->store('cnis', 'public')
                : null;

            $contratPath = $request->hasFile('contrat')
                ? $request->file('contrat')->store('contrats', 'public')
                : null;

            // Logique pour le RIB : Si chèque, on met "Non applicable" ou null
            $ribValue = ($request->choix_paiement === 'Chèques') ? null : $request->rib;

            // Création du propriétaire
            $owner = Proprietaire::create([
                'code_id' => $codeId,
                'name' => $validatedData['name'],
                'prenom' => $validatedData['prenom'],
                'email' => $validatedData['email'],
                'contact' => $validatedData['contact'],
                'commune' => $validatedData['commune'],
                'pourcentage' => $validatedData['pourcentage'],
                'choix_paiement' => $validatedData['choix_paiement'],
                'rib' => $ribValue,
                'contrat' => $contratPath,
                'password' => Hash::make('password'),
                'profil_image' => $profileImagePath,
                'cni' => $cniPath, // Attention: bien utiliser $cniPath (nom variable corrigé)
                'agence_id' => $agenceId
            ]);

            // Envoi SMS de bienvenue
            $this->sendOwnerWelcomeSms($owner, $agence);

            return redirect()->route('owner.index')->with('success', 'Propriétaire enregistré avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur création propriétaire: ' . $e->getMessage());
            // Retourner l'erreur exacte pour le débogage (à retirer en prod si besoin)
            return back()->withErrors(['error' => 'Erreur système : ' . $e->getMessage()])->withInput();
        }
    }

/**
 * Envoi SMS de bienvenue au propriétaire
 */
private function sendOwnerWelcomeSms(Proprietaire $owner, $agence)
{
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
        $phoneNumber = $this->formatPhoneNumberForSms($owner->contact);

        // Message personnalisé
        $smsContent = "Bonjour {$owner->prenom} {$owner->name},\n"
                    . "Bienvenue chez {$agence->name}! Votre compte propriétaire a été créé.\n"
                    . "Code: {$owner->code_id}\n"
                    . "Email: {$owner->email}\n"
                    . "Contact: {$agence->contact}";

        $message = $twilio->messages->create(
            $phoneNumber,
            [
                'from' => env('TWILIO_PHONE_NUMBER'),
                'body' => $smsContent,
            ]
        );

        Log::channel('sms')->info('SMS bienvenue envoyé', [
            'proprietaire_id' => $owner->id,
            'to' => $phoneNumber,
            'message_sid' => $message->sid
        ]);

    } catch (TwilioException $e) {
        Log::channel('sms')->error('Erreur SMS bienvenue', [
            'proprietaire_id' => $owner->id,
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Formatage des numéros pour SMS
 */
private function formatPhoneNumberForSms(string $phone): string
{
    $cleaned = preg_replace('/[^0-9+]/', '', $phone);
    
    // Formatage spécifique pour la Côte d'Ivoire
    if (str_starts_with($cleaned, '+225') && strlen($cleaned) === 12) {
        return $cleaned;
    }
    
    $cleaned = ltrim($cleaned, '+');
    $cleaned = preg_replace('/^00/', '', $cleaned);
    
    // Extraction des derniers chiffres
    $baseNumber = substr($cleaned, -8);
    
    if (!preg_match('/^[0-9]{8,15}$/', $baseNumber)) {
        throw new \Exception('Numéro de téléphone invalide');
    }
    
    return '+225' . $baseNumber;
}

    public function edit($id)
    {
        $proprietaire = Proprietaire::findOrFail($id);
         $agenceId = Auth::guard('agence')->user()->code_id;
        // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')
                        ->whereHas('bien', function ($query) use ($agenceId) {
                            $query->where('agence_id', $agenceId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        return view('agence.proprietaire.edit', compact('proprietaire', 'pendingVisits'));
    }

     public function update(Request $request, $id)
    {
        $proprietaire = Proprietaire::findOrFail($id);

        // Validation des données
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:proprietaires,email,'.$proprietaire->id,
            'contact' => 'required|string|min:10',
            'commune' => 'required|string|max:255',
            'pourcentage' => 'required|integer|between:1,15',
            'choix_paiement' => 'required|in:Virement Bancaire,Chèques',
            'rib' => $request->choix_paiement == 'Virement Bancaire' ? 'required|string|max:255' : 'nullable',
            'contrat' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ],[
            'name.required' => 'Le nom du propriétaire est obligatoire.',
            'prenom.required' => 'Le prénom du propriétaire est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'contact.required' => 'Le contact est obligatoire.',
            'contact.min' => 'Le contact doit avoir au moins 10 chiffres.',
            'commune.required' => 'La commune est obligatoire.',
            'pourcentage.required' => 'Le pourcentage est obligatoire.',
            'pourcentage.between' => 'Le pourcentage doit être entre 1% et 15%.',
            'choix_paiement.required' => 'Le mode de paiement est obligatoire.',
            'rib.required' => 'Le RIB est obligatoire pour les virements bancaires.',
            'contrat.mimes' => 'Le contrat doit être un fichier PDF, DOC ou DOCX.',
            'contrat.max' => 'Le contrat ne doit pas dépasser 2Mo.',
        ]);

        try {
            // Mise à jour des informations de base
            $proprietaire->name = $validatedData['name'];
            $proprietaire->prenom = $validatedData['prenom'];
            $proprietaire->email = $validatedData['email'];
            $proprietaire->contact = $validatedData['contact'];
            $proprietaire->commune = $validatedData['commune'];
            $proprietaire->pourcentage = $validatedData['pourcentage'];
            $proprietaire->choix_paiement = $validatedData['choix_paiement'];
            $proprietaire->rib = $validatedData['choix_paiement'] == 'Virement Bancaire' ? $validatedData['rib'] : null;

            // Gestion du fichier de contrat
            if ($request->hasFile('contrat')) {
                // Supprimer l'ancien contrat s'il existe
                if ($proprietaire->contrat && Storage::exists($proprietaire->contrat)) {
                    Storage::delete($proprietaire->contrat);
                }
                
                // Stocker le nouveau contrat
                $contratPath = $request->file('contrat')->store('contrats_proprietaires');
                $proprietaire->contrat = $contratPath;
            }

            $proprietaire->save();

            return redirect()->route('owner.index')
                ->with('success', 'Propriétaire mis à jour avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du propriétaire: ' . $e->getMessage());
            return back()
                ->with('error', 'Une erreur est survenue lors de la mise à jour. Veuillez réessayer.')
                ->withInput();
        }
    }

    public function destroyAdmin($id)
    {
        try {
            DB::beginTransaction(); // Début de la transaction

            $proprietaire = Proprietaire::findOrFail($id);
            
            // 1. Supprimer tous les abonnements associés
            Abonnement::where('proprietaire_id', $proprietaire->code_id)->delete();
            
            // 2. Supprimer le RIB si existant
            if ($proprietaire->rib) {
                Storage::delete('public/' . $proprietaire->rib);
            }
            
            // 3. Supprimer le propriétaire
            $proprietaire->delete();
            
            DB::commit(); // Validation de la transaction

            return redirect()->back()
                ->with('success', 'Propriétaire et ses abonnements supprimés avec succès.');

        } catch (\Exception $e) {
            DB::rollBack(); // Annulation en cas d'erreur
            Log::error('Erreur suppression propriétaire: '.$e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression : '.$e->getMessage());
        }
    }
    public function destroy(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $proprietaire = Proprietaire::findOrFail($id);
            $agence = Auth::guard('agence')->user();

            // 1. Récupération LARGE des locataires (Sans filtrer le statut pour être sûr)
            // Cela garantit que si la Vue a vu des locataires, le Contrôleur les voit aussi.
            $locatairesActifs = Locataire::where('proprietaire_id', $proprietaire->code_id)->get();

            // 2. Si des locataires existent, on déclenche la sécurité et l'archivage
            if ($locatairesActifs->isNotEmpty()) {
                
                // A. Vérification du code
                if (!$request->filled('validation_code')) {
                    return redirect()->back()->with('error', 'Sécurité : Code manquant alors que des locataires existent.');
                }

                // On nettoie le code (trim) et on compare
                if (trim($request->input('validation_code')) !== $agence->code_id) {
                    return redirect()->back()->with('error', 'Code de sécurité incorrect. Suppression annulée.');
                }

                // B. Archivage dans HistoriqueBien
                foreach ($locatairesActifs as $locataire) {
                    $bien = $locataire->bien; 
                    
                    HistoriqueBien::create([
                        'agence_code' => $agence->code_id,
                        'proprietaire_code' => $proprietaire->code_id,
                        // Utilisation d'opérateurs null safe au cas où des données manquent
                        'proprietaire_nom_complet' => ($proprietaire->name ?? '') . ' ' . ($proprietaire->prenom ?? ''),
                        'bien_type' => $bien ? $bien->type : 'Inconnu',
                        'bien_commune' => $bien ? $bien->commune : 'Inconnue',
                        'bien_prix' => $bien ? $bien->prix : 0,
                        'locataire_nom_complet' => ($locataire->name ?? '') . ' ' . ($locataire->prenom ?? ''),
                        'locataire_contact' => $locataire->contact ?? 'N/A',
                        'date_suppression' => now(),
                    ]);
                }
            }

            // 3. Suppression des fichiers physiques
            if ($proprietaire->rib) {
                Storage::disk('public')->delete($proprietaire->rib);
            }
            if ($proprietaire->contrat) {
                Storage::disk('public')->delete($proprietaire->contrat);
            }
            
            // 4. Suppression finale (Cascading delete gérera les locataires/biens si configuré en BDD)
            // Sinon décommenter : 
            // Locataire::where('proprietaire_id', $proprietaire->code_id)->delete();
            // Bien::where('proprietaire_id', $proprietaire->code_id)->delete();
            
            $proprietaire->delete();
            
            DB::commit();
            return redirect()->back()->with('success', 'Propriétaire supprimé (Historique archivé si nécessaire).');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression propriétaire : ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur technique : ' . $e->getMessage());
        }
    }
    public function defineAccess($email){
        //Vérification si le sous-admin existe déjà
        $checkSousadminExiste = Proprietaire::where('email', $email)->first();
        if($checkSousadminExiste){
            return view('proprietaire.auth.validate', compact('email'));
        }else{
            return redirect()->route('owner.login')->with('error', 'Email inconnu');
        };
    }

    public function submitDefineAccess(Request $request)
    {
        // Validation des données
        $request->validate([
            'code' => 'required|exists:reset_code_password_proprietaires,code',
            'password' => 'required|same:password_confirm',
            'password_confirm' => 'required|same:password',
        ], [
            'code.exists' => 'Le code de réinitialisation est invalide.',
            'code.required' => 'Le code de réinitialisation est obligatoire. Veuillez vérifier votre email.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.same' => 'Les mots de passe doivent être identiques.',
            'password_confirm.same' => 'Les mots de passe doivent être identiques.',
            'password_confirm.required' => 'Le mot de passe de confirmation est obligatoire.',
        ]);
    
        try {
            $locataire = Proprietaire::where('email', $request->email)->first();
    
            if ($locataire) {
                // Mise à jour du mot de passe
                $locataire->password = Hash::make($request->password);
                $locataire->update();
    
                if ($locataire) {
                    $existingcodelocataire = ResetCodePasswordProprietaire::where('email', $locataire->email)->count();
    
                    if ($existingcodelocataire > 1) {
                        ResetCodePasswordProprietaire::where('email', $locataire->email)->delete();
                    }
                }
    
                return redirect()->route('owner.login')->with('success', 'Compte mis à jour avec succès');
            } else {
                return redirect()->route('owner.login')->with('error', 'Email inconnu');
            }
        } catch (\Exception $e) {
            Log::error('Error updating admin profile: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue : ' . $e->getMessage())->withInput();
        }
    }

    public function login(){
        return view('proprietaire.auth.login');
     }

   public function authenticate(Request $request)
    {
            $request->validate([
                'email' => 'required|exists:proprietaires,email',
                'password' => 'required|min:8',
            ], [
                'email.required' => 'Le mail est obligatoire.',
                'email.exists' => 'Cette adresse mail n\'existe pas.',
                'password.required' => 'Le mot de passe est obligatoire.',
                'password.min' => 'Le mot de passe doit avoir au moins 8 caractères.',
            ]);

            try {
            // 1. Authentification
            if (!auth('owner')->attempt($request->only('email', 'password'))) {
                return redirect()->back()
                            ->with('error', 'Email ou mot de passe incorrect.')
                            ->withInput($request->only('email'));
            }

            // 2. Récupérer l'utilisateur connecté
            $proprietaire = auth('owner')->user();

            // 3. Vérifier l'abonnement
            $abonnement = Abonnement::where('proprietaire_id', $proprietaire->code_id)
                                ->latest('date_fin')
                                ->first();

            // 4. Conditions pour accéder au dashboard :
            // - Abonnement existe
            // - Statut = "actif" 
            // - Date de fin non dépassée
            if ($abonnement && $abonnement->statut === 'actif' && $abonnement->date_fin >= now()) {
                return redirect()->route('owner.dashboard')
                            ->with('success', 'Bienvenue sur votre tableau de bord');
            }

            // 5. Tous les autres cas -> page abonnement
            return redirect()->route('page.abonnement')
                        ->with('error', $this->getAbonnementMessage($abonnement));

        } catch (Exception $e) {
            Log::error('Connexion échouée : '.$e->getMessage());
            auth('owner')->logout();
            
            return back()->with('error', 'Erreur technique - Veuillez réessayer')
                        ->withInput($request->only('email'));
        }
    }

private function getAbonnementMessage($abonnement): string
{
    if (!$abonnement) {
        return 'Aucun abonnement actif trouvé';
    }

    return match ($abonnement->statut) {
        'en_attente' => 'Votre paiement est en cours de validation',
        'suspendu'   => 'Votre compte est suspendu',
        'actif'     => $abonnement->date_fin < now() 
                        ? 'Votre abonnement a expiré' 
                        : 'Abonnement requis',
        default      => 'Statut d\'abonnement non reconnu',
    };
}
      public function logout(){
        auth('owner')->logout();
        return redirect()->route('owner.login')->with('success', 'Déconnexion réussie.');
    }




    //les routes pour les proprietaires gerer par l'administrateur
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
        $agenceId = Auth::guard('admin')->user()->id;
        $proprietaires = Proprietaire::whereNull('agence_id')->paginate(6);
        return view('admin.proprietaire.index',compact('proprietaires', 'pendingVisits'));
    }

    public function createAdmin()
    {  // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')
                        ->whereHas('bien', function ($query) {
                             $query->whereNull('agence_id');  // Filtrer par l'ID de l'agence
                             $query->whereNull('proprietaire_id') // 1er cas: bien sans propriétaire
                                ->orWhereHas('proprietaire', function($q) {
                                    $q->where('gestion', 'agence'); // 2ème cas: bien avec propriétaire gestion agence
                                });
                        })
                        ->count();
        return view('admin.proprietaire.create', compact('pendingVisits'));
    }

   public function storeAdmin(Request $request)
{
    // Validation des données avec messages personnalisés
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'prenom' => 'required|string|max:255',
        'email' => 'required|email|unique:proprietaires,email',
        'contact' => 'required|string|min:10',
        'commune' => 'required|string|max:255',
        'rib' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        'diaspora' => 'nullable|string',
        'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ], [
        'name.required' => 'Le nom du proprietaire est obligatoire.',
        'prenom.required' => 'Le prénom du proprietaire est obligatoire.',
        'email.required' => 'L\'adresse e-mail est obligatoire.',
        'email.email' => 'L\'adresse e-mail n\'est pas valide.',
        'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
        'contact.required' => 'Le contact est obligatoire.',
        'contact.min' => 'Le contact doit avoir au moins 10 chiffres.',
        'commune.required' => 'Lieu de residence est obligatoire.',
        'rib.max' => 'Le fichier RIB ne doit pas dépasser 2Mo.',
        'rib.mimes' => 'Le RIB doit être au format PDF, JPG ou PNG.',
        'profile_image.image' => 'Le fichier doit être une image.',
        'profile_image.mimes' => 'L\'image doit être au format JPEG, PNG ou JPG.',
        'profile_image.max' => 'L\'image ne doit pas dépasser 2Mo.',
    ]);

    DB::beginTransaction();

    try {
        Log::info('Début de la création du propriétaire', ['email' => $request->email]);

        $adminId = Auth::guard('admin')->user()->id;

        // Génération du code PRO unique
        do {
            $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $codeId = 'PRO' . $randomNumber;
            Log::debug('Génération code PRO', ['code' => $codeId]);
        } while (Proprietaire::where('code_id', $codeId)->exists());

        // Traitement de l'image de profil
        $profileImagePath = null;
        if ($request->hasFile('profile_image')) {
            try {
                $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
                Log::info('Image profil enregistrée', ['path' => $profileImagePath]);
            } catch (\Exception $e) {
                Log::error('Erreur enregistrement image profil', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                throw new \Exception("Erreur lors de l'enregistrement de l'image de profil");
            }
        }

         // Traitement de la pièce d'identité
        $cniImagePath = null;
        if ($request->hasFile('cni')) {
            try {
                $cniImagePath = $request->file('cni')->store('cnis', 'public');
                Log::info('Cni enregistrée', ['path' => $cniImagePath]);
            } catch (\Exception $e) {
                Log::error('Erreur enregistrement la pièce', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                throw new \Exception("Erreur lors de l'enregistrement de la pièce");
            }
        }


        // Traitement du fichier RIB
        $ribPath = null;
        if ($request->hasFile('rib')) {
            try {
                $ribPath = $request->file('rib')->store('ribs', 'public');
                Log::info('RIB enregistré', ['path' => $ribPath]);
            } catch (\Exception $e) {
                Log::error('Erreur enregistrement RIB', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                throw new \Exception("Erreur lors de l'enregistrement du RIB");
            }
        }

        // Création du Proprietaire
        $ownerData = [
            'code_id' => $codeId,
            'name' => $validatedData['name'],
            'prenom' => $validatedData['prenom'],
            'email' => $validatedData['email'],
            'contact' => $validatedData['contact'],
            'commune' => $validatedData['commune'],
            'rib' => $ribPath,
            'choix_paiement' => 'Virement Bancaire',
            'password' => Hash::make('password'),
            'profil_image' => $profileImagePath,
            'cni' => $cniImagePath,
            'diaspora' => $request->input('diaspora', '0') === '1' ? 'Oui' : 'Non',
            'gestion' => 'proprietaire',
        ];

        Log::debug('Données du propriétaire', $ownerData);

        $owner = Proprietaire::create($ownerData);
        Log::info('Propriétaire créé', ['id' => $owner->id]);

        /************************************************
         * CRÉATION AUTOMATIQUE DE L'ABONNEMENT
         ************************************************/
        $today = now();
        $dateDebut = $today->format('Y-m-d');
        $dateFin = $today->copy()->addMonth(3)->format('Y-m-d'); // Abonnement d'3 mois offert lors de l'inscription
        
        $abonnementData = [
            'proprietaire_id' => $owner->code_id,
            'date_abonnement' => $today,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'mois_abonne' => $today->format('m-Y'),
            'montant' => 0, // À ajuster selon votre logique métier
            'montant_actuel' => 0, // À ajuster selon votre logique métier
            'statut' => 'actif',
            'type' => 'standard',
            'mode_paiement' => 'offert', // Ou autre valeur par défaut
            'reference_paiement' => 'CREA-' . $owner->code_id,
            'notes' => 'Abonnement créé automatiquement lors de l\'inscription',
        ];

        Abonnement::create($abonnementData);
        Log::info('Abonnement créé', ['proprietaire_id' => $owner->code_id]);

        // Envoi de l'e-mail de vérification si gestion par propriétaire
        if ($owner->gestion === 'proprietaire') {
            try {
                ResetCodePasswordProprietaire::where('email', $owner->email)->delete();
                
                $code = rand(1000, 4000);
                ResetCodePasswordProprietaire::create([
                    'code' => $code,
                    'email' => $owner->email,
                ]);

                Notification::route('mail', $owner->email)
                    ->notify(new SendEmailToOwnerAfterRegistrationNotification($code, $owner->email));
                
                Log::info('Email de vérification envoyé', ['email' => $owner->email]);
            } catch (\Exception $e) {
                Log::error('Erreur envoi email', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                // On continue malgré l'erreur d'email
            }
        }

        DB::commit();

        return redirect()->route('owner.index.admin')
            ->with('success', 'Propriétaire enregistré avec succès avec son abonnement initial.');

    } catch (\Exception $e) {
        DB::rollBack();
        
        Log::error('Erreur création propriétaire', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'request' => $request->all()
        ]);

        return back()
            ->withErrors(['error' => 'Une erreur est survenue lors de la création. Veuillez réessayer.'])
            ->withInput()
            ->with('error_message', $e->getMessage());
    }
}

    public function editAdmin($id)
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
        $proprietaire = Proprietaire::findOrFail($id);
        return view('admin.proprietaire.edit', compact('proprietaire' , 'pendingVisits'));
    }

  public function updateAdmin(Request $request, $id)
    {
        $proprietaire = Proprietaire::findOrFail($id);

        // Validation des données
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:proprietaires,email,'.$proprietaire->id,
            'contact' => 'required|string|min:10',
            'commune' => 'required|string|max:255',
            'rib' => 'nullable|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gestion' => 'nullable|boolean',
            'diaspora' => 'nullable|boolean',
        ],[
            'name.required' => 'Le nom du proprietaire est obligatoire.',
            'prenom.required' => 'Le prénom du proprietaire est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'contact.required' => 'Le contact est obligatoire.',
            'contact.min' => 'Le contact doit avoir au moins 10 chiffres.',
            'commune.required' => 'La commune est obligatoire.',
            'rib.max' => 'La rib ne doit pas dépasser 255 caractères.',
            'profile_image.image' => 'Le fichier doit être une image.',
            'profile_image.mimes' => 'L\'image doit être de type: jpeg, png, jpg ou gif.',
            'profile_image.max' => 'L\'image ne doit pas dépasser 2Mo.',
        ]);

        try {
            // Traitement de l'image de profil
            if ($request->hasFile('profile_image')) {
                // Supprimer l'ancienne image si elle existe
                if ($proprietaire->profile_image) {
                    Storage::disk('public')->delete($proprietaire->profile_image);
                }
                $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
                $validatedData['profile_image'] = $profileImagePath;
            }

             // Traitement du rib
             $ribPath = null;
            if ($request->hasFile('rib')) {
                $ribPath = $request->file('rib')->store('ribs', 'public');
            }

            // Gestion du type de gestion (par agence ou par propriétaire)
            $gestionParAgence = $request->has('gestion') && $request->gestion;
            $gestionValue = $gestionParAgence ? 'agence' : 'proprietaire';

            // Gestion du type de gestion (par agence ou par propriétaire)
            $gestionDiaspora = $request->has('diaspora') && $request->diaspora;
            $gestionDisporaValue = $gestionDiaspora ? 'Oui' : 'Non';

            // Mise à jour des informations
            $proprietaire->update([
                'name' => $validatedData['name'],
                'prenom' => $validatedData['prenom'],
                'email' => $validatedData['email'],
                'contact' => $validatedData['contact'],
                'commune' => $validatedData['commune'],
                'gestion' => $gestionValue,
                'diaspora' => $gestionDisporaValue,
                'profile_image' => $validatedData['profile_image'] ?? $proprietaire->profile_image
            ]);

            return redirect()->route('owner.index.admin')
                ->with('success', 'Proprietaire de bien mis à jour avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du proprietaire: ' . $e->getMessage());
            return back()
                ->with('error', 'Une erreur est survenue lors de la mise à jour. Veuillez réessayer.')
                ->withInput();
        }
    }


}
