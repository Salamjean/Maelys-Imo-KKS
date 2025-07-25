<?php

namespace App\Http\Controllers\Agence;
use App\Http\Controllers\Controller;
use App\Models\Abonnement;
use App\Models\Agence;
use App\Models\Bien;
use App\Models\Paiement;
use App\Models\ResetCodePasswordAgence;
use App\Models\Reversement;
use App\Models\Visite;
use App\Notifications\SendEmailToAgenceAfterRegistrationNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class AgenceController extends Controller
{
     private function calculerSoldeDisponible($agenceId)
        {
            $totalPaiements = Paiement::where('methode_paiement', 'Mobile Money')
                ->whereHas('bien', function($query) use ($agenceId) {
                    $query->where('agence_id', $agenceId);
                })
                ->where('statut', 'payé')
                ->sum('montant');
            
            $totalReversements = Reversement::where('agence_id', $agenceId)
                ->sum('montant');
            
            return $totalPaiements - $totalReversements;
        }
    public function dashboard()
    {
       if (!auth('agence')->check()) {
            return redirect()->route('agence.login');
       }
       $agenceId = Auth::guard('agence')->user()->code_id;
        // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')
                        ->whereHas('bien', function ($query) use ($agenceId) {
                            $query->where('agence_id', $agenceId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        // Récupération de l'agence connectée
        $agence = auth('agence')->user();
        // Totaux généraux
        $totalAppartements = Bien::where('type', 'Appartement')
                            ->where('agence_id', $agence->code_id)
                            ->count();
        $totalMaisons = Bien::where('type', 'Maison')
                        ->where('agence_id', $agence->code_id)
                        ->count();
        $totalMagasins = Bien::where('type', 'Bureau')
                        ->where('agence_id', $agence->code_id)   
                        ->count();
        
        // Statistiques par période
        $stats = [
            'day' => [
                'appartements' => Bien::where('type', 'Appartement')->where('agence_id', $agence->code_id)->whereDate('created_at', today())->count(),
                'maisons' => Bien::where('type', 'Maison')->where('agence_id', $agence->code_id)->whereDate('created_at', today())->count(),
                'magasins' => Bien::where('type', 'Bureau')->where('agence_id', $agence->code_id)->whereDate('created_at', today())->count()
            ],
            'week' => [
                'appartements' => Bien::where('type', 'Appartement')->where('agence_id', $agence->code_id)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'maisons' => Bien::where('type', 'Maison')->where('agence_id', $agence->code_id)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'magasins' => Bien::where('type', 'Bureau')->where('agence_id', $agence->code_id)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count()
            ],
            'month' => [
                'appartements' => Bien::where('type', 'Appartement')->where('agence_id', $agence->code_id)->whereMonth('created_at', now()->month)->count(),
                'maisons' => Bien::where('type', 'Maison')->where('agence_id', $agence->code_id)->whereMonth('created_at', now()->month)->count(),
                'magasins' => Bien::where('type', 'Bureau')->where('agence_id', $agence->code_id)->whereMonth('created_at', now()->month)->count()
            ]
        ];
    
        $soldeDisponible = $this->calculerSoldeDisponible($agenceId);
        // Biens récents
        $recentBiens = Bien::with('agence')
                          ->where('agence_id', $agence->code_id)
                          ->orderBy('created_at', 'desc')
                          ->take(5)
                          ->get();
    
        return view('agence.dashboard', [
            'totalAppartements' => $totalAppartements,
            'totalMaisons' => $totalMaisons,
            'totalMagasins' => $totalMagasins,
            'stats' => $stats,
            'recentBiens' => $recentBiens,
            'pendingVisits' => $pendingVisits,
            'soldeDisponible' => $soldeDisponible
        ]);
    }
    public function index()
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
        // Récupération de toutes les agences
        $agences = Agence::paginate(6);
        return view('admin.agence.index', compact('agences', 'pendingVisits'));
    }

    public function create()
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
        return view('admin.agence.create', compact('pendingVisits'));
    }

    public function store(Request $request)
    {
        // Validation des données
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:agences,email',
            'contact' => 'required|string|min:10',
            'commune' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'rib' => 'required|file|mimes:pdf|max:2048',
            'rccm' => 'required|string|max:255',
            'rccm_file' => 'required|file|mimes:pdf|max:2048',
            'dfe' => 'required|string|max:255',
            'dfe_file' => 'required|file|mimes:pdf|max:2048',
        ],[
            'name.required' => 'Le nom de l\'agence est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'contact.required' => 'Le contact est obligatoire.',
            'contact.min' => 'Le contact doit avoir au moins 10 chiffres.',
            'commune.required' => 'La commune est obligatoire.',
            'adresse.required' => 'L\'adresse est obligatoire.',
            'rib.required' => 'Le RIB est obligatoire.',
            'rib.max' => 'Le RIB ne doit pas dépasser 2048 caractères.',
            'rib.mines' => 'le fichier doit etre un pdf',
            
        ]);
    
        try {
            // Génération du code PRO unique
            do {
                $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
                $codeId = 'AG' . $randomNumber;
            } while (Agence::where('code_id', $codeId)->exists());

            // Traitement de l'image de profil
            $profileImagePath = null;
            if ($request->hasFile('profile_image')) {
                $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
            }

            $ribPath = null;
            if ($request->hasFile('rib')) {
                $ribPath = $request->file('rib')->store('ribs', 'public');
            }
            $rccmPath = null;
            if ($request->hasFile('rccm_file')) {
                $rccmPath = $request->file('rccm_file')->store('rccm_files', 'public');
            }
            $dfe_filePath = null;
            if ($request->hasFile('dfe_file')) {
                $dfe_filePath = $request->file('dfe_file')->store('dfe_files', 'public');
            }
    
            // Création de l'agence
            $agence = new Agence();
            $agence->code_id = $codeId;
            $agence->name = $request->name;
            $agence->email = $request->email;
            $agence->contact = $request->contact;
            $agence->commune = $request->commune;
            $agence->adresse = $request->adresse;
            $agence->rccm_file = $rccmPath;
            $agence->dfe_file = $dfe_filePath;
            $agence->rccm = $request->rccm;
            $agence->dfe = $request->dfe;
            $agence->rib = $ribPath;
            $agence->password = Hash::make('password');
            $agence->profile_image = $profileImagePath;
            $agence->save();

        /************************************************
           CRÉATION AUTOMATIQUE DE L'ABONNEMENT
         ************************************************/
        $today = now();
        $dateDebut = $today->format('Y-m-d');
        $dateFin = $today->copy()->addMonth(3)->format('Y-m-d'); // Abonnement d'3 mois offert lors de l'inscription
        
        $abonnementData = [
            'agence_id' => $agence->code_id,
            'date_abonnement' => $today,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'mois_abonne' => $today->format('m-Y'),
            'montant' => 0, // À ajuster selon votre logique métier
            'montant_actuel' => 0, // À ajuster selon votre logique métier
            'statut' => 'actif',
            'type' => 'standard',
            'mode_paiement' => 'offert', // Ou autre valeur par défaut
            'reference_paiement' => 'CREA-' . $agence->code_id,
            'notes' => 'Abonnement créé automatiquement lors de l\'inscription',
        ];

        Abonnement::create($abonnementData);
        Log::info('Abonnement créé', ['agence_id' => $agence->code_id]);
    
            // Envoi de l'e-mail de vérification
            ResetCodePasswordAgence::where('email', $agence->email)->delete();
            $code = rand(1000, 4000);
            ResetCodePasswordAgence::create([
                'code' => $code,
                'email' => $agence->email,
            ]);

            Notification::route('mail', $agence->email)
                ->notify(new SendEmailToAgenceAfterRegistrationNotification($code, $agence->email));
        
            return redirect()->route('agence.index')
                ->with('success', 'Agence enregistrée avec succès.');
    
        } catch (\Exception $e) {
            Log::error('Error creating agence: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
        }
    }

    public function edit($id)
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
        $agence = Agence::findOrFail($id);
        return view('admin.agence.edit', compact('agence', 'pendingVisits'));
    }

    public function update(Request $request, $id)
    {
        $agence = Agence::findOrFail($id);

        // Validation des données
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:agences,email,'.$agence->id,
            'contact' => 'required|string|min:10',
            'commune' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'rib' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'rccm' => 'required|string|max:255',
            'rccm_file' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'dfe' => 'required|string|max:255',
            'dfe_file' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ],[
            'name.required' => 'Le nom de l\'agence est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'contact.required' => 'Le contact est obligatoire.',
            'contact.min' => 'Le contact doit avoir au moins 10 chiffres.',
            'commune.required' => 'La commune est obligatoire.',
            'adresse.required' => 'L\'adresse est obligatoire.',
            'rccm.required' => 'Le numéro RCCM est obligatoire.',
            'dfe.required' => 'Le numéro DFE est obligatoire.',
            'rib.mimes' => 'Le RIB doit être un fichier PDF, JPEG, JPG ou PNG.',
            'rccm_file.mimes' => 'Le fichier RCCM doit être un fichier PDF, JPEG, JPG ou PNG.',
            'dfe_file.mimes' => 'Le fichier DFE doit être un fichier PDF, JPEG, JPG ou PNG.',
            'profile_image.mimes' => 'L\'image de profil doit être un fichier JPEG, PNG, JPG ou GIF.',
        ]);

        try {
            // Traitement de l'image de profil
            if ($request->hasFile('profile_image')) {
                // Supprimer l'ancienne image si elle existe
                if ($agence->profile_image) {
                    Storage::disk('public')->delete($agence->profile_image);
                }
                $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
                $agence->profile_image = $profileImagePath;
            }

            // Traitement du fichier RIB
            if ($request->hasFile('rib')) {
                // Supprimer l'ancien fichier si il existe
                if ($agence->rib) {
                    Storage::disk('public')->delete($agence->rib);
                }
                $ribPath = $request->file('rib')->store('ribs', 'public');
                $agence->rib = $ribPath;
            }

            // Traitement du fichier RCCM
            if ($request->hasFile('rccm_file')) {
                // Supprimer l'ancien fichier si il existe
                if ($agence->rccm_file) {
                    Storage::disk('public')->delete($agence->rccm_file);
                }
                $rccmFilePath = $request->file('rccm_file')->store('rccm_files', 'public');
                $agence->rccm_file = $rccmFilePath;
            }

            // Traitement du fichier DFE
            if ($request->hasFile('dfe_file')) {
                // Supprimer l'ancien fichier si il existe
                if ($agence->dfe_file) {
                    Storage::disk('public')->delete($agence->dfe_file);
                }
                $dfeFilePath = $request->file('dfe_file')->store('dfe_files', 'public');
                $agence->dfe_file = $dfeFilePath;
            }

            // Mise à jour des informations
            $agence->name = $request->name;
            $agence->email = $request->email;
            $agence->contact = $request->contact;
            $agence->commune = $request->commune;
            $agence->adresse = $request->adresse;
            $agence->rccm = $request->rccm;
            $agence->dfe = $request->dfe;
            $agence->save();

            return redirect()->route('agence.index')
                ->with('success', 'Agence mise à jour avec succès.');

        } catch (\Exception $e) {
            Log::error('Error updating agence: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
        }
    }

    // Function to define access for the agence
    public function defineAccess($email){
        //Vérification si le sous-admin existe déjà
        $checkSousadminExiste = Agence::where('email', $email)->first();
        if($checkSousadminExiste){
            return view('agence.auth.validate', compact('email'));
        }else{
            return redirect()->route('agence.login')->with('error', 'Email inconnu');
        };
    }

    public function submitDefineAccess(Request $request)
    {
        // Validation des données
        $request->validate([
            'code' => 'required|exists:reset_code_password_agences,code',
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
            $agence = Agence::where('email', $request->email)->first();
    
            if ($agence) {
                // Mise à jour du mot de passe
                $agence->password = Hash::make($request->password);
                $agence->update();
    
                if ($agence) {
                    $existingcodeagence = ResetCodePasswordAgence::where('email', $agence->email)->count();
    
                    if ($existingcodeagence > 1) {
                        ResetCodePasswordAgence::where('email', $agence->email)->delete();
                    }
                }
    
                return redirect()->route('agence.login')->with('success', 'Compte mis à jour avec succès');
            } else {
                return redirect()->route('agence.login')->with('error', 'Email inconnu');
            }
        } catch (\Exception $e) {
            Log::error('Error updating admin profile: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue : ' . $e->getMessage())->withInput();
        }
    }

    public function login(){
        // Vérifier si l'utilisateur est déjà authentifié
        if (auth('agence')->check()) {
            return redirect()->route('agence.dashboard');
        }
        return view('agence.auth.login');
     }
    
     public function authenticate(Request $request)
     {
         // Validation des champs du formulaire
         $request->validate([
             'email' => 'required|exists:agences,email',
             'password' => 'required|min:8',
         ], [
             'email.required' => 'Le mail est obligatoire.',
             'email.exists' => 'Cette adresse mail n\'existe pas.',
             'password.required' => 'Le mot de passe est obligatoire.',
             'password.min' => 'Le mot de passe doit avoir au moins 8 caractères.',
         ]);
     
          try {
            // 1. Authentification
            if (!auth('agence')->attempt($request->only('email', 'password'))) {
                return redirect()->back()
                            ->with('error', 'Email ou mot de passe incorrect.')
                            ->withInput($request->only('email'));
            }

            // 2. Récupérer l'utilisateur connecté
            $agence = auth('agence')->user();

            // 3. Vérifier l'abonnement
            $abonnement = Abonnement::where('agence_id', $agence->code_id)
                                ->latest('date_fin')
                                ->first();

            // 4. Conditions pour accéder au dashboard :
            // - Abonnement existe
            // - Statut = "actif" 
            // - Date de fin non dépassée
            if ($abonnement && $abonnement->statut === 'actif' && $abonnement->date_fin >= now()) {
                return redirect()->route('agence.dashboard')
                            ->with('success', 'Bienvenue sur votre tableau de bord');
            }

            // 5. Tous les autres cas -> page abonnement
            return redirect()->route('page.abonnement.agence')
                        ->with('error', $this->getAbonnementMessage($abonnement));

        } catch (Exception $e) {
            Log::error('Connexion échouée : '.$e->getMessage());
            auth('agence')->logout();
            
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
        auth('agence')->logout();
        return redirect()->route('agence.login')->with('success', 'Déconnexion réussie.');
    }

    public function editProfile()
    {
        $agenceId = Auth::guard('agence')->user()->code_id;
        // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')
                        ->whereHas('bien', function ($query) use ($agenceId) {
                            $query->where('agence_id', $agenceId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        $agence = Auth::guard('agence')->user();
        return view('agence.auth.profile', compact('agence', 'pendingVisits'));
    }

    public function updateProfile(Request $request)
    {
        $agence = Auth::guard('agence')->user();
    
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:agences,email,'.$agence->id,
            'contact' => 'required|string|min:10',
            'commune' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    
        // Ajoute les règles de validation pour le mot de passe seulement si un nouveau mot de passe est fourni
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|same:password_confirm';
            $rules['password_confirm'] = 'string|min:8|same:password';
        }
    
        $request->validate($rules, [
            'password.same' => 'Les mots de passe ne correspondent pas',
            'password_confirm.same' => 'Les mots de passe ne correspondent pas',
        ]);
    
        try {
            // Mise à jour de l'image de profil
            if ($request->hasFile('profile_image')) {
                if ($agence->profile_image) {
                    Storage::disk('public')->delete($agence->profile_image);
                }
                $agence->profile_image = $request->file('profile_image')->store('profile_images', 'public');
            }
    
            // Mise à jour des informations de base
            $agence->name = $request->name;
            $agence->email = $request->email;
            $agence->contact = $request->contact;
            $agence->commune = $request->commune;
            $agence->adresse = $request->adresse;
    
            // Mise à jour du mot de passe seulement si fourni
            if ($request->filled('password')) {
                $agence->password = Hash::make($request->password);
            }
    
            $agence->save();
    
            return redirect()->route('agence.dashboard')->with('success', 'Vos informations on bien été mis à jour!');
    
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour profil agence: '.$e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la mise à jour');
        }
    }

   public function destroy($id)
    {
        try {
            DB::beginTransaction(); // Début de la transaction

            $agence = Agence::findOrFail($id);
            
            // 1. Supprimer tous les abonnements associés
            Abonnement::where('agence_id', $agence->code_id)->delete();
            
            // 2. Supprimer les fichiers associés (RIB + image de profil)
            if ($agence->rib) {
                Storage::delete('public/' . $agence->rib);
            }
            if ($agence->profile_image) {
                Storage::delete('public/' . $agence->profile_image);
            }
            
            // 3. Supprimer l'agence
            $agence->delete();
            
            DB::commit(); // Validation de la transaction

            return redirect()->back()
                ->with('success', 'Agence et ses abonnements supprimés avec succès.');

        } catch (\Exception $e) {
            DB::rollBack(); // Annulation en cas d'erreur
            Log::error('Erreur suppression agence: '.$e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression : '.$e->getMessage());
        }
    }
}
