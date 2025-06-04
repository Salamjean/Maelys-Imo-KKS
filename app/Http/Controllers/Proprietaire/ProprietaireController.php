<?php

namespace App\Http\Controllers\Proprietaire;
use App\Http\Controllers\Controller;


use App\Models\Bien;
use App\Models\Proprietaire;
use App\Models\ResetCodePasswordProprietaire;
use App\Notifications\SendEmailToOwnerAfterRegistrationNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class ProprietaireController extends Controller
{
    public function dashboard()
    {
        // Vérifier si l'utilisateur est connecté en tant que propriétaire
        $proprietaireId = Auth::guard('owner')->user()->id;
        
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

        return view('proprietaire.dashboard', compact(
            'totalBiens',
            'cumulLoyers',
            'biensDisponibles',
            'biensOccupes',
            'pourcentageDisponibles',
            'pourcentageOccupes',
            'derniersBiens'
        ));
    }
    public function index(){
        $agenceId = Auth::guard('agence')->user()->id;
        $proprietaires = Proprietaire::where('agence_id', $agenceId)->paginate(6);
        return view('agence.proprietaire.index',compact('proprietaires'));
    }

    public function create()
    {
        return view('agence.proprietaire.create');
    }

    public function store(Request $request)
    {
        // Validation des données
        $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:proprietaires,email',
            'contact' => 'required|string|min:10',
            'commune' => 'required|string|max:255',
            'fonction' => 'nullable|max:255',
        ],[
            'name.required' => 'Le nom du proprietaire est obligatoire.',
            'prenom.required' => 'Le prénom du proprietaire est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'contact.required' => 'Le contact est obligatoire.',
            'contact.min' => 'Le contact doit avoir au moins 10 chiffres.',
            'commune.required' => 'Lieu de residence est obligatoire.',
            'fonction.max' => 'La fonction ne doit pas dépasser 255 caractères.',
        ]);
    
        try {
            $agenceId = Auth::guard('agence')->user()->id;
            // Traitement de l'image de profil
            $profileImagePath = null;
            if ($request->hasFile('profile_image')) {
                $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
            }
    
            // Création de l'agence
            $owner = new Proprietaire();
            $owner->name = $request->name;
            $owner->prenom = $request->prenom;
            $owner->email = $request->email;
            $owner->contact = $request->contact;
            $owner->commune = $request->commune;
            $owner->fonction = $request->fonction;
            $owner->password = Hash::make('password');
            $owner->profil_image = $profileImagePath;
            $owner->agence_id = $agenceId;
            $owner->save();
    
            // Envoi de l'e-mail de vérification
            ResetCodePasswordProprietaire::where('email', $owner->email)->delete();
            $code = rand(1000, 4000);
            ResetCodePasswordProprietaire::create([
                'code' => $code,
                'email' => $owner->email,
            ]);

            Notification::route('mail', $owner->email)
                ->notify(new SendEmailToOwnerAfterRegistrationNotification($code, $owner->email));
        
            return redirect()->route('owner.index')
                ->with('success', 'Propriétaire de bien enregistrée avec succès.');
    
        } catch (\Exception $e) {
            Log::error('Error creating propriataire: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        $proprietaire = Proprietaire::findOrFail($id);
        return view('agence.proprietaire.edit', compact('proprietaire'));
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
            'fonction' => 'nullable|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ],[
            'name.required' => 'Le nom du proprietaire est obligatoire.',
            'prenom.required' => 'Le prénom du proprietaire est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'contact.required' => 'Le contact est obligatoire.',
            'contact.min' => 'Le contact doit avoir au moins 10 chiffres.',
            'commune.required' => 'La commune est obligatoire.',
            'fonction.max' => 'La fonction ne doit pas dépasser 255 caractères.',
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

            // Mise à jour des informations
            $proprietaire->update([
                'name' => $validatedData['name'],
                'prenom' => $validatedData['prenom'],
                'email' => $validatedData['email'],
                'contact' => $validatedData['contact'],
                'commune' => $validatedData['commune'],
                'fonction' => $validatedData['fonction'],
                'profile_image' => $validatedData['profile_image'] ?? $proprietaire->profile_image
            ]);

            return redirect()->route('owner.index')
                ->with('success', 'Proprietaire de bien mis à jour avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du proprietaire: ' . $e->getMessage());
            return back()
                ->with('error', 'Une erreur est survenue lors de la mise à jour. Veuillez réessayer.')
                ->withInput();
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
         // Validation des champs du formulaire
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
            if(auth('owner')->attempt($request->only('email', 'password')))
            {
                return redirect()->route('owner.dashboard')->with('Bienvenu sur votre page ');
            }else{
                return redirect()->back()->with('error', 'Mot de passe incorrect.');
            }
        } catch (Exception $e) {
            dd($e);
        }
     }

      public function logout(){
        auth('owner')->logout();
        return redirect()->route('owner.login')->with('success', 'Déconnexion réussie.');
    }




    //les routes pour les proprietaires gerer par l'administrateur
     public function indexAdmin(){
        $agenceId = Auth::guard('admin')->user()->id;
        $proprietaires = Proprietaire::whereNull('agence_id')->paginate(6);
        return view('admin.proprietaire.index',compact('proprietaires'));
    }

    public function createAdmin()
    {
        return view('admin.proprietaire.create');
    }

    public function storeAdmin(Request $request)
    {
        // Validation des données
        $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:proprietaires,email',
            'contact' => 'required|string|min:10',
            'commune' => 'required|string|max:255',
            'fonction' => 'nullable|max:255',
        ],[
            'name.required' => 'Le nom du proprietaire est obligatoire.',
            'prenom.required' => 'Le prénom du proprietaire est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'contact.required' => 'Le contact est obligatoire.',
            'contact.min' => 'Le contact doit avoir au moins 10 chiffres.',
            'commune.required' => 'Lieu de residence est obligatoire.',
            'fonction.max' => 'La fonction ne doit pas dépasser 255 caractères.',
        ]);
    
        try {
            $agenceId = Auth::guard('admin')->user()->id;
            // Traitement de l'image de profil
            $profileImagePath = null;
            if ($request->hasFile('profile_image')) {
                $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
            }
    
            // Création de l'agence
            $owner = new Proprietaire();
            $owner->name = $request->name;
            $owner->prenom = $request->prenom;
            $owner->email = $request->email;
            $owner->contact = $request->contact;
            $owner->commune = $request->commune;
            $owner->fonction = $request->fonction;
            $owner->password = Hash::make('password');
            $owner->profil_image = $profileImagePath;
            $owner->save();
    
            // Envoi de l'e-mail de vérification
            ResetCodePasswordProprietaire::where('email', $owner->email)->delete();
            $code = rand(1000, 4000);
            ResetCodePasswordProprietaire::create([
                'code' => $code,
                'email' => $owner->email,
            ]);

            Notification::route('mail', $owner->email)
                ->notify(new SendEmailToOwnerAfterRegistrationNotification($code, $owner->email));
        
            return redirect()->route('owner.index.admin')
                ->with('success', 'Propriétaire de bien enregistrée avec succès.');
    
        } catch (\Exception $e) {
            Log::error('Error creating propriataire: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
        }
    }

    public function editAdmin($id)
    {
        $proprietaire = Proprietaire::findOrFail($id);
        return view('admin.proprietaire.edit', compact('proprietaire'));
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
            'fonction' => 'nullable|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ],[
            'name.required' => 'Le nom du proprietaire est obligatoire.',
            'prenom.required' => 'Le prénom du proprietaire est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'contact.required' => 'Le contact est obligatoire.',
            'contact.min' => 'Le contact doit avoir au moins 10 chiffres.',
            'commune.required' => 'La commune est obligatoire.',
            'fonction.max' => 'La fonction ne doit pas dépasser 255 caractères.',
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

            // Mise à jour des informations
            $proprietaire->update([
                'name' => $validatedData['name'],
                'prenom' => $validatedData['prenom'],
                'email' => $validatedData['email'],
                'contact' => $validatedData['contact'],
                'commune' => $validatedData['commune'],
                'fonction' => $validatedData['fonction'],
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
