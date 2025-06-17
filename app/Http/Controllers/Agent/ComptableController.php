<?php

namespace App\Http\Controllers\Agent;
use App\Http\Controllers\Controller;

use App\Models\Bien;
use App\Models\Comptable;
use App\Models\Locataire;
use App\Models\Paiement;
use App\Models\ResetCodePasswordComptable;
use App\Notifications\SendEmailToComptableAfterRegistrationNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class ComptableController extends Controller
{
    public function dashboard()
    {
        Carbon::setLocale('fr');
        $comptable = Auth::guard('comptable')->user();
        // 1. Statistiques de base
        if($comptable->agence_id){
            $totalLocataires = Locataire::where('agence_id', $comptable->agence_id)->count();
        }else{
            $totalLocataires = Locataire::whereNull('agence_id')
                                        ->whereNull('proprietaire_id') // 1er cas: bien sans propriétaire
                                            ->orWhereHas('proprietaire', function($q) {
                                                $q->where('gestion', 'agence'); // 2ème cas: bien avec propriétaire gestion agence
                                            })
                                        ->count();
        }

        if($comptable->agence_id){
            $locatairesActifs = Locataire::where('agence_id', $comptable->agence_id)
                                        ->where('status', 'Actif')->
                                        count();
        }else{
            $locatairesActifs = Locataire::whereNull('agence_id')
                                            ->whereNull('proprietaire_id') // 1er cas: bien sans propriétaire
                                            ->orWhereHas('proprietaire', function($q) {
                                                $q->where('gestion', 'agence'); // 2ème cas: bien avec propriétaire gestion agence
                                            })
                                            ->where('status', 'Actif')
                                            ->count();
        }
        
        // 2. Statistiques des biens (optimisé en une seule requête)
        if($comptable->agence_id){
            $biensStats = Bien::where('agence_id', $comptable->agence_id)
                            ->selectRaw("COUNT(*) as total, 
                                SUM(CASE WHEN status = 'Loué' THEN 1 ELSE 0 END) as Loué, 
                                SUM(CASE WHEN status = 'Disponible' THEN 1 ELSE 0 END) as Disponible")
                            ->first();
        }else{
            $biensStats = Bien::whereNull('agence_id')
                             ->whereNull('proprietaire_id') // 1er cas: bien sans propriétaire
                                            ->orWhereHas('proprietaire', function($q) {
                                                $q->where('gestion', 'agence'); // 2ème cas: bien avec propriétaire gestion agence
                                            })
                            ->selectRaw("COUNT(*) as total, 
                                SUM(CASE WHEN status = 'Loué' THEN 1 ELSE 0 END) as Loué, 
                                SUM(CASE WHEN status = 'Disponible' THEN 1 ELSE 0 END) as Disponible")
                            ->first();
        }
        $biensLoues = $biensStats['Loué'] ?? 0;
        $biensDisponibles = $biensStats['Disponible'] ?? 0;
        
        // 3. Statistiques financières
    if($comptable->agence_id) {
        $paiementsStats = Paiement::whereHas('bien', function($query) use ($comptable) {
                $query->where('agence_id', $comptable->agence_id);
            })
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN statut = 'payé' THEN 1 ELSE 0 END) as count_paye,
                SUM(CASE WHEN statut = 'En attente' THEN 1 ELSE 0 END) as count_attente,
                SUM(CASE WHEN statut = 'payé' THEN montant ELSE 0 END) as total_paye,
                SUM(CASE WHEN statut = 'En attente' THEN montant ELSE 0 END) as total_attente,
                SUM(CASE WHEN mois_couvert = ? AND statut = 'payé' THEN montant ELSE 0 END) as mois_courant,
                SUM(CASE WHEN mois_couvert LIKE ? AND statut = 'payé' THEN montant ELSE 0 END) as annee_courante",
                [now()->format('Y-m'), now()->year.'-%']
            )
            ->first();
    } else {
        $paiementsStats = Paiement::whereHas('bien', function($query) {
                $query->whereNull('agence_id')
                    ->whereNull('proprietaire_id')
                    ->orWhereHas('proprietaire', function($q) {
                        $q->where('gestion', 'agence');
                    });
            })
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN statut = 'payé' THEN 1 ELSE 0 END) as count_paye,
                SUM(CASE WHEN statut = 'En attente' THEN 1 ELSE 0 END) as count_attente,
                SUM(CASE WHEN statut = 'payé' THEN montant ELSE 0 END) as total_paye,
                SUM(CASE WHEN statut = 'En attente' THEN montant ELSE 0 END) as total_attente,
                SUM(CASE WHEN mois_couvert = ? AND statut = 'payé' THEN montant ELSE 0 END) as mois_courant,
                SUM(CASE WHEN mois_couvert LIKE ? AND statut = 'payé' THEN montant ELSE 0 END) as annee_courante",
                [now()->format('Y-m'), now()->year.'-%']
            )
            ->first();
    }

    // Extraction des valeurs
    $loyersMoisCourant = $paiementsStats->mois_courant ?? 0;
    $loyersAnneeCourante = $paiementsStats->annee_courante ?? 0;
    $totalPaiementsPayes = $paiementsStats->total_paye ?? 0;
    $paiementsEnAttente = $paiementsStats->count_attente ?? 0; // Correction ici

// 2. Derniers paiements (pour l'activité récente)
$recentPayments = Paiement::with(['locataire', 'bien'])
    ->where('statut', 'payé')
    ->when($comptable->agence_id, function($query) use ($comptable) {
        $query->whereHas('bien', function($q) use ($comptable) {
            $q->where('agence_id', $comptable->agence_id);
        });
    }, function($query) {
        $query->whereHas('bien', function($q) {
            $q->whereNull('agence_id')
              ->whereNull('proprietaire_id')
              ->orWhereHas('proprietaire', function($subQ) {
                  $subQ->where('gestion', 'agence');
              });
        });
    })
    ->orderBy('date_paiement', 'desc')
    ->take(5)
    ->get();

// 3. Données pour le graphique mensuel (12 derniers mois)
$startDate = now()->subMonths(11)->startOfMonth();
$endDate = now()->endOfMonth();

$loyersParMois = Paiement::whereHas('bien', function($query) use ($comptable) {
        if($comptable->agence_id) {
            $query->where('agence_id', $comptable->agence_id);
        } else {
            $query->whereNull('agence_id')
                ->whereNull('proprietaire_id')
                ->orWhereHas('proprietaire', function($q) {
                    $q->where('gestion', 'agence');
                });
        }
    })
    ->selectRaw("
        DATE_FORMAT(mois_couvert, '%Y-%m') as mois, 
        SUM(montant) as total"
    )
    ->where('statut', 'payé')
    ->whereBetween('mois_couvert', [
        $startDate->format('Y-m'),
        $endDate->format('Y-m')
    ])
    ->groupBy('mois')
    ->orderBy('mois')
    ->get()
    ->keyBy('mois');

// Formatage des données pour Chart.js
$labels = [];
$data = [];

for ($i = 11; $i >= 0; $i--) {
    $date = now()->subMonths($i);
    $moisKey = $date->format('Y-m');
    $label = $date->translatedFormat('M Y');
    
    $labels[] = $label;
    $data[] = $loyersParMois->has($moisKey) ? (int)$loyersParMois[$moisKey]->total : 0;
}
        
        return view('comptable.dashboard', compact(
            'totalLocataires',
            'locatairesActifs',
            'biensLoues',
            'biensDisponibles',
            'loyersMoisCourant',
            'loyersAnneeCourante',
            'paiementsEnAttente',
            'recentPayments',
            'labels',
            'data'
        ));
    }

    public function destroy($id)
    {
        try {
            $comptable = Comptable::findOrFail($id);
            
            // Supprimer le RIB si existant
            if ($comptable->rib) {
                Storage::delete('public/' . $comptable->rib);
            }
            
            $comptable->delete();
            
            return redirect()->back()->with('success', 'Comptable supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression du comptable.');
        }
    }
    public function destroyAgence($id)
    {
        try {
            $comptable = Comptable::findOrFail($id);
            
            // Supprimer le RIB si existant
            if ($comptable->rib) {
                Storage::delete('public/' . $comptable->rib);
            }
            
            $comptable->delete();
            
            return redirect()->back()->with('success', 'Comptable supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression du comptable.');
        }
    }
    public function index(){
        $agenceId = Auth::guard('agence')->user()->code_id;
        $comptables = Comptable::where('agence_id', $agenceId)->paginate(6);
        return view('agence.comptable.index', compact('comptables'));
    }

    public function create(){
        return view('agence.comptable.create');
    }

    public function store(Request $request)
    {
        // Validation des données
        $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:comptables,email',
            'contact' => 'required|string|min:10',
            'commune' => 'required|string|max:255',
            'user_type' =>'required',
            'date_naissance' => 'required|max:255',
        ],[
            'name.required' => 'Le nom du comptable est obligatoire.',
            'prenom.required' => 'Le prénom du comptable est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'contact.required' => 'Le contact est obligatoire.',
            'contact.min' => 'Le contact doit avoir au moins 10 chiffres.',
            'commune.required' => 'Lieu de residence est obligatoire.',
            'date_naissance.required' => 'La date de naissance est obligatoire.',
            'user_type.required' => 'Le type d\'agent est obligatoire'
        ]);
    
        try {
            $agenceId = Auth::guard('agence')->user()->code_id;
            // Traitement de l'image de profil
            $profileImagePath = null;
            if ($request->hasFile('profile_image')) {
                $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
            }
    
            // Création de l'agence
            $comptable = new Comptable();
            $comptable->name = $request->name;
            $comptable->prenom = $request->prenom;
            $comptable->email = $request->email;
            $comptable->contact = $request->contact;
            $comptable->commune = $request->commune;
            $comptable->date_naissance = $request->date_naissance;
            $comptable->password = Hash::make('password');
            $comptable->user_type = $request->user_type;
            $comptable->profile_image = $profileImagePath;
            $comptable->agence_id = $agenceId;
            $comptable->save();
    
            // Envoi de l'e-mail de vérification
            ResetCodePasswordComptable::where('email', $comptable->email)->delete();
            $code = rand(1000, 4000);
            ResetCodePasswordComptable::create([
                'code' => $code,
                'email' => $comptable->email,
            ]);

            Notification::route('mail', $comptable->email)
                ->notify(new SendEmailToComptableAfterRegistrationNotification($code, $comptable->email));
        
            return redirect()->route('accounting.index')
                ->with('success', 'Agent enregistrée avec succès.');
    
        } catch (\Exception $e) {
            Log::error('Error creating Agent: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        $comptable = Comptable::findOrFail($id);
        return view('agence.comptable.edit', compact('comptable'));
    }

     public function update(Request $request, $id)
    {
        $comptable = Comptable::findOrFail($id);

        // Validation des données
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:comptables,email,'.$comptable->id,
            'contact' => 'required|string|min:10',
            'commune' => 'required|string|max:255',
            'date_naissance' => 'required|date',
            'user_type' => 'required',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ],[
            'name.required' => 'Le nom du comptable est obligatoire.',
            'prenom.required' => 'Le prénom du comptable est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'contact.required' => 'Le contact est obligatoire.',
            'contact.min' => 'Le contact doit avoir au moins 10 chiffres.',
            'commune.required' => 'La commune est obligatoire.',
            'date_naissance.required' => 'La date de naissance est obligatoire.',
            'date_naissance.date' => 'La date de naissance doit être une date valide.',
            'profile_image.image' => 'Le fichier doit être une image.',
            'profile_image.mimes' => 'L\'image doit être de type: jpeg, png, jpg ou gif.',
            'profile_image.max' => 'L\'image ne doit pas dépasser 2Mo.',
            'user_type.required' => 'Le type d\'agent est obligatoire'
        ]);

        try {
            // Traitement de l'image de profil
            if ($request->hasFile('profile_image')) {
                // Supprimer l'ancienne image si elle existe
                if ($comptable->profile_image) {
                    Storage::disk('public')->delete($comptable->profile_image);
                }
                $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
                $validatedData['profile_image'] = $profileImagePath;
            }

            // Mise à jour des informations
            $comptable->update([
                'name' => $validatedData['name'],
                'prenom' => $validatedData['prenom'],
                'email' => $validatedData['email'],
                'contact' => $validatedData['contact'],
                'commune' => $validatedData['commune'],
                'date_naissance' => $validatedData['date_naissance'],
                'user_type' => $validatedData['user_type'],
                'profile_image' => $validatedData['profile_image'] ?? $comptable->profile_image
            ]);

            return redirect()->route('accounting.index')
                ->with('success', 'Agent mis à jour avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du comptable: ' . $e->getMessage());
            return back()
                ->with('error', 'Une erreur est survenue lors de la mise à jour. Veuillez réessayer.')
                ->withInput();
        }
    }

    public function defineAccess($email){
        //Vérification si le sous-admin existe déjà
        $checkSousadminExiste = Comptable::where('email', $email)->first();
        if($checkSousadminExiste){
            return view('comptable.auth.validate', compact('email'));
        }else{
            return redirect()->route('comptable.login')->with('error', 'Email inconnu');
        };
    }

    public function submitDefineAccess(Request $request)
    {
        // Validation des données
        $request->validate([
            'code' => 'required|exists:reset_code_password_comptables,code',
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
            $comptable = Comptable::where('email', $request->email)->first();
    
            if ($comptable) {
                // Mise à jour du mot de passe
                $comptable->password = Hash::make($request->password);
                $comptable->update();
    
                if ($comptable) {
                    $existingcodecomptable = ResetCodePasswordComptable::where('email', $comptable->email)->count();
    
                    if ($existingcodecomptable > 1) {
                        ResetCodePasswordComptable::where('email', $comptable->email)->delete();
                    }
                }
    
                return redirect()->route('comptable.login')->with('success', 'Compte mis à jour avec succès');
            } else {
                return redirect()->route('comptable.login')->with('error', 'Email inconnu');
            }
        } catch (\Exception $e) {
            Log::error('Error updating admin profile: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue : ' . $e->getMessage())->withInput();
        }
    }

     public function login(){
        return view('comptable.auth.login');
     }

     public function authenticate(Request $request)
     {
         // Validation des champs du formulaire
         $request->validate([
             'email' => 'required|exists:comptables,email',
             'password' => 'required|min:8',
         ], [
             'email.required' => 'Le mail est obligatoire.',
             'email.exists' => 'Cette adresse mail n\'existe pas.',
             'password.required' => 'Le mot de passe est obligatoire.',
             'password.min' => 'Le mot de passe doit avoir au moins 8 caractères.',
         ]);
     
         try {
            if (auth('comptable')->attempt($request->only('email', 'password'))) {
                $user = auth('comptable')->user();
                
                if ($user->user_type === 'Agent de recouvrement') {
                    return redirect()->route('accounting.agent.dashboard')->with('success', 'Bienvenue sur votre page Agent');
                } elseif ($user->user_type === 'Comptable') {
                    return redirect()->route('accounting.dashboard')->with('success', 'Bienvenue sur votre page Comptable');
                } else {
                    return redirect()->back()->with('error', 'Type d\'utilisateur inconnu.');
                }
            } else {
                return redirect()->back()->with('error', 'Mot de passe incorrect.');
            }

        } catch (Exception $e) {
            dd($e);
        }
     }

      public function logout(){
        auth('comptable')->logout();
        return redirect()->route('comptable.login')->with('success', 'Déconnexion réussie.');
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
            $locataires = Locataire::with(['bien', 'paiements', 'agence'])
                ->whereNull('agence_id')
                ->whereNull('proprietaire_id')
                ->get();;
        }
        
        return view('comptable.locataire.index', compact('locataires'));
    }

    public function payment(){
         // Récupérer le comptable connecté
        $comptable = Auth::guard('comptable')->user();

        // Récupérer l'agence du comptable
        $agence = $comptable->agence;

        // Récupération des locataires de l'agence du comptable

        // Vérifier si le comptable a une agence associée
        if ($comptable->agence_id) {
             $locataires = Locataire::with(['bien', 'paiements' => function($query) {
                        $query->whereMonth('created_at', now()->month)
                            ->whereYear('created_at', now()->year);
                        }])
                        ->where('status', '!=', 'Pas sérieux')
                        ->where('agence_id', $agence->code_id)
                        ->paginate(6);
        } else {
            // Si le comptable n'a pas d'agence, retourner une collection vide
            $locataires = Locataire::with(['bien', 'paiements' => function($query) {
                        $query->whereMonth('created_at', now()->month)
                            ->whereYear('created_at', now()->year);
                        }])
                        ->where('status', '!=', 'Pas sérieux')
                        ->whereNull('agence_id')
                        ->whereNull('proprietaire_id')
                        ->paginate(6);
        }
       

        // Ajout d'une propriété à chaque locataire pour afficher ou non le bouton
        $locataires->getCollection()->transform(function($locataire) {
            $today = now()->format('d');
            $currentMonthPaid = $locataire->paiements->isNotEmpty();
            $locataire->show_reminder_button = ($locataire->bien->date_fixe == $today) && !$currentMonthPaid;
            return $locataire;
        });
        return view('comptable.locataire.paiement',compact('locataires'));
    }




    // les fonctions de gestions des comptables par l'administrateur 

    public function indexAdmin(){
        $agenceId = Auth::guard('admin')->user()->id;
        $comptables = Comptable::whereNull('agence_id')->paginate(6);
        return view('admin.comptable.index', compact('comptables'));
    }

    public function createAdmin(){
        return view('admin.comptable.create');
    }

    public function storeAdmin(Request $request)
    {
        // Validation des données
        $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:comptables,email',
            'contact' => 'required|string|min:10',
            'commune' => 'required|string|max:255',
            'user_type' =>'required',
            'date_naissance' => 'required|max:255',
        ],[
            'name.required' => 'Le nom du comptable est obligatoire.',
            'prenom.required' => 'Le prénom du comptable est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'contact.required' => 'Le contact est obligatoire.',
            'contact.min' => 'Le contact doit avoir au moins 10 chiffres.',
            'commune.required' => 'Lieu de residence est obligatoire.',
            'date_naissance.required' => 'La date de naissance est obligatoire.',
            'user_type.required' => 'Le type d\'agent est obligatoire'
        ]);
    
        try {
            $agenceId = Auth::guard('admin')->user()->id;
            // Traitement de l'image de profil
            $profileImagePath = null;
            if ($request->hasFile('profile_image')) {
                $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
            }
    
            // Création de l'agence
            $comptable = new Comptable();
            $comptable->name = $request->name;
            $comptable->prenom = $request->prenom;
            $comptable->email = $request->email;
            $comptable->contact = $request->contact;
            $comptable->commune = $request->commune;
            $comptable->date_naissance = $request->date_naissance;
            $comptable->password = Hash::make('password');
            $comptable->user_type = $request->user_type;
            $comptable->profile_image = $profileImagePath;
            $comptable->save();
    
            // Envoi de l'e-mail de vérification
            ResetCodePasswordComptable::where('email', $comptable->email)->delete();
            $code = rand(1000, 4000);
            ResetCodePasswordComptable::create([
                'code' => $code,
                'email' => $comptable->email,
            ]);

            Notification::route('mail', $comptable->email)
                ->notify(new SendEmailToComptableAfterRegistrationNotification($code, $comptable->email));
        
            return redirect()->route('accounting.index.admin')
                ->with('success', 'Agent enregistrée avec succès.');
    
        } catch (\Exception $e) {
            Log::error('Error creating Agent: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
        }
    }

    public function editAdmin($id)
    {
        $comptable = Comptable::findOrFail($id);
        return view('admin.comptable.edit', compact('comptable'));
    }

     public function updateAdmin(Request $request, $id)
    {
        $comptable = Comptable::findOrFail($id);

        // Validation des données
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:comptables,email,'.$comptable->id,
            'contact' => 'required|string|min:10',
            'commune' => 'required|string|max:255',
            'date_naissance' => 'required|date',
            'user_type' => 'required',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ],[
            'name.required' => 'Le nom du comptable est obligatoire.',
            'prenom.required' => 'Le prénom du comptable est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'contact.required' => 'Le contact est obligatoire.',
            'contact.min' => 'Le contact doit avoir au moins 10 chiffres.',
            'commune.required' => 'La commune est obligatoire.',
            'date_naissance.required' => 'La date de naissance est obligatoire.',
            'date_naissance.date' => 'La date de naissance doit être une date valide.',
            'profile_image.image' => 'Le fichier doit être une image.',
            'profile_image.mimes' => 'L\'image doit être de type: jpeg, png, jpg ou gif.',
            'profile_image.max' => 'L\'image ne doit pas dépasser 2Mo.',
            'user_type.required' => 'Le type d\'agent est obligatoire'
        ]);

        try {
            // Traitement de l'image de profil
            if ($request->hasFile('profile_image')) {
                // Supprimer l'ancienne image si elle existe
                if ($comptable->profile_image) {
                    Storage::disk('public')->delete($comptable->profile_image);
                }
                $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
                $validatedData['profile_image'] = $profileImagePath;
            }

            // Mise à jour des informations
            $comptable->update([
                'name' => $validatedData['name'],
                'prenom' => $validatedData['prenom'],
                'email' => $validatedData['email'],
                'contact' => $validatedData['contact'],
                'commune' => $validatedData['commune'],
                'date_naissance' => $validatedData['date_naissance'],
                'user_type' => $validatedData['user_type'],
                'profile_image' => $validatedData['profile_image'] ?? $comptable->profile_image
            ]);

            return redirect()->route('accounting.index.admin')
                ->with('success', 'Agent mis à jour avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du comptable: ' . $e->getMessage());
            return back()
                ->with('error', 'Une erreur est survenue lors de la mise à jour. Veuillez réessayer.')
                ->withInput();
        }
    }
}
