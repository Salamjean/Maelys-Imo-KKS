<?php

namespace App\Http\Controllers\Agence;
use App\Http\Controllers\Controller;

use App\Models\Agence;
use App\Models\Bien;
use App\Models\ResetCodePasswordAgence;
use App\Notifications\SendEmailToAgenceAfterRegistrationNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class AgenceController extends Controller
{
    public function dashboard()
    {
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
            'recentBiens' => $recentBiens
        ]);
    }
    public function index()
    {
        // Récupération de toutes les agences
        $agences = Agence::paginate(6);
        return view('admin.agence.index', compact('agences'));
    }

    public function create()
    {
        return view('admin.agence.create');
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
            'rib.mines' => 'le fichier doit etre un pdf'
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
    
            // Création de l'agence
            $agence = new Agence();
            $agence->code_id = $codeId;
            $agence->name = $request->name;
            $agence->email = $request->email;
            $agence->contact = $request->contact;
            $agence->commune = $request->commune;
            $agence->adresse = $request->adresse;
            $agence->rib = $ribPath;
            $agence->password = Hash::make('password');
            $agence->profile_image = $profileImagePath;
            $agence->save();
    
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
        $agence = Agence::findOrFail($id);
        return view('admin.agence.edit', compact('agence'));
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
            'rib' => 'nullable|string|max:255',
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
            'rib.max' => 'Le RIB ne doit pas dépasser 255 caractères.',
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

            // Mise à jour des informations
            $agence->name = $request->name;
            $agence->email = $request->email;
            $agence->contact = $request->contact;
            $agence->commune = $request->commune;
            $agence->adresse = $request->adresse;
            $agence->rib = $request->rib;
            $agence->save();

            return redirect()->route('agence.index')
                ->with('success', 'Agence mise à jour avec succès.');

        } catch (\Exception $e) {
            Log::error('Error updating agence: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
        }
    }
    public function destroy($id)
    {
        // Logic to delete the agence data
        return redirect()->route('agence.index')->with('success', 'Agence deleted successfully.');
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
            if(auth('agence')->attempt($request->only('email', 'password')))
            {
                return redirect()->route('agence.dashboard')->with('Bienvenu sur votre page ');
            }else{
                return redirect()->back()->with('error', 'Mot de passe incorrect.');
            }
        } catch (Exception $e) {
            dd($e);
        }
     }

    public function logout(){
        auth('agence')->logout();
        return redirect()->route('agence.login')->with('success', 'Déconnexion réussie.');
    }

    public function editProfile()
    {
        $agence = Auth::guard('agence')->user();
        return view('agence.auth.profile', compact('agence'));
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
}
