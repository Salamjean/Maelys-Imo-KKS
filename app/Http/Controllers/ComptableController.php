<?php

namespace App\Http\Controllers;

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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class ComptableController extends Controller
{
    public function dashboard()
    {
        Carbon::setLocale('fr');
        // 1. Statistiques de base
        $totalLocataires = Locataire::count();
        $locatairesActifs = Locataire::where('status', 'Actif')->count();
        
        // 2. Statistiques des biens (optimisé en une seule requête)
        $biensStats = Bien::selectRaw('status, count(*) as count')
                ->whereIn('status', ['Loué', 'Disponible'])
                ->groupBy('status')
                ->pluck('count', 'status');
        
        $biensLoues = $biensStats['Loué'] ?? 0;
        $biensDisponibles = $biensStats['Disponible'] ?? 0;
        
        // 3. Statistiques financières
        $paiementsStats = Paiement::selectRaw(
            "SUM(CASE WHEN mois_couvert = ? AND statut = 'payé' THEN montant ELSE 0 END) as mois_courant,
            SUM(CASE WHEN mois_couvert LIKE ? AND statut = 'payé' THEN montant ELSE 0 END) as annee_courante",
            [now()->format('Y-m'), now()->year.'-%']
        )->first();
        
        $loyersMoisCourant = $paiementsStats->mois_courant ?? 0;
        $loyersAnneeCourante = $paiementsStats->annee_courante ?? 0;
        
        $paiementsEnAttente = Paiement::where('statut', 'En attente')->count();
        
        // 4. Derniers paiements (pour l'activité récente)
        $recentPayments = Paiement::with(['locataire', 'bien'])
                            ->where('statut', 'payé')
                            ->orderBy('date_paiement', 'desc')
                            ->take(5)
                            ->get();
        
        // 5. Données pour le graphique mensuel (12 derniers mois)
    $startDate = now()->subMonths(11)->startOfMonth();
    $endDate = now()->endOfMonth();

    // Récupérer tous les mois avec leur total, même si 0
    $loyersParMois = Paiement::selectRaw(
        "DATE_FORMAT(mois_couvert, '%Y-%m') as mois, 
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
    public function index(){
        $agenceId = Auth::guard('agence')->user()->id;
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
        ]);
    
        try {
            $agenceId = Auth::guard('agence')->user()->id;
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
                ->with('success', 'Comptable enregistrée avec succès.');
    
        } catch (\Exception $e) {
            Log::error('Error creating comptable: ' . $e->getMessage());
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
                'profile_image' => $validatedData['profile_image'] ?? $comptable->profile_image
            ]);

            return redirect()->route('accounting.index')
                ->with('success', 'Comptable mis à jour avec succès.');

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
            if(auth('comptable')->attempt($request->only('email', 'password')))
            {
                return redirect()->route('accounting.dashboard')->with('Bienvenu sur votre page ');
            }else{
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
            $locataires = collect();
        }
        
        return view('comptable.locataire.index', compact('locataires'));
    }

    public function payment(){
        // Récupération des locataires avec les relations nécessaires
        $locataires = Locataire::with(['bien', 'paiements' => function($query) {
            $query->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        }])
        ->where('status', '!=', 'Pas sérieux')
        ->where('agence_id', Auth::guard('comptable')->user()->id)
        ->paginate(6);

        // Ajout d'une propriété à chaque locataire pour déterminer si le bouton doit être affiché
        $locataires->getCollection()->transform(function($locataire) {
            $today = now()->format('d');
            $currentMonthPaid = $locataire->paiements->isNotEmpty();
            $locataire->show_reminder_button = ($locataire->bien->date_fixe == $today) && !$currentMonthPaid;
            return $locataire;
        });
        return view('comptable.locataire.paiement',compact('locataires'));
    }
}
