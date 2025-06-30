<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use App\Models\Agence;
use App\Models\Proprietaire;
use App\Models\ResetCodePasswordAgence;
use App\Notifications\SendEmailToAgenceAfterRegistrationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class HomePageController extends Controller
{
    public function about(){
        return view('home.about');
    }
    public function service(){
        return view('home.service');
    }

    public function RegisterAgence(){
        return view('agence.home.register');
    }

     public function storeAgence(Request $request)
    { 
        // Validation des données
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:agences,email',
            'contact' => 'required|string|min:10',
            'commune' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'password' => 'required|confirmed|min:8',
            'rib' => 'nullable|file|mimes:pdf|max:2048',
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
            'rib.required' => 'Le RIB est obligatoire.',
            'rib.max' => 'Le RIB ne doit pas dépasser 2048 caractères.',
            'rib.mines' => 'le fichier doit etre un pdf',
            'profile_image.image' => 'Le fichier de l\'image de profil doit être une image.',
            'profile_image.mimes' => 'L\'image de profil doit être au format jpeg, png, jpg ou gif.',
            'profile_image.max' => 'L\'image de profil ne doit pas dépasser 2 Mo.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min' => 'Le mot de passe doit comporter au moins 8 caractères'
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
            $agence->password = Hash::make($request->password);
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
            'montant' => 10000, // À ajuster selon votre logique métier
            'statut' => 'actif',
            'mode_paiement' => 'offert', // Ou autre valeur par défaut
            'reference_paiement' => 'CREA-' . $agence->code_id,
            'notes' => 'Abonnement créé automatiquement lors de l\'inscription',
        ];

        Abonnement::create($abonnementData);
        Log::info('Abonnement créé', ['agence_id' => $agence->code_id]);
        
            return redirect()->route('agence.login')
                ->with('success', 'Votre agence a été enregistré avec succès, veuillez-vous connecter.');
    
        } catch (\Exception $e) {
            Log::error('Error creating agence: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
        }
    }



    /// Les fonctions d'enregistrement d'un propriétaire par lui meme 
    public function RegisterOwner(){
        return view('proprietaire.home.register');
    }

       public function storeOwner(Request $request)
{
    // Validation des données avec messages personnalisés
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'prenom' => 'required|string|max:255',
        'email' => 'required|email|unique:proprietaires,email',
        'contact' => 'required|string|min:10',
        'commune' => 'required|string|max:255',
        'adresse' => 'required|string|max:255',
        'password' => 'required|confirmed|min:8',
        'rib' => 'nullable|file|mimes:pdf|max:2048',
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
            'choix_paiement' => 'RIB',
            'password' => Hash::make($request->input('password')), // Mot de passe par défaut si non fourni
            'profil_image' => $profileImagePath,
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
            'montant' => 10000, // À ajuster selon votre logique métier
            'statut' => 'actif',
            'mode_paiement' => 'offert', // Ou autre valeur par défaut
            'reference_paiement' => 'CREA-' . $owner->code_id,
            'notes' => 'Abonnement créé automatiquement lors de l\'inscription',
        ];

        Abonnement::create($abonnementData);
        Log::info('Abonnement créé', ['proprietaire_id' => $owner->code_id]);

        // // Envoi de l'e-mail de vérification si gestion par propriétaire
        // if ($owner->gestion === 'proprietaire') {
        //     try {
        //         ResetCodePasswordProprietaire::where('email', $owner->email)->delete();
                
        //         $code = rand(1000, 4000);
        //         ResetCodePasswordProprietaire::create([
        //             'code' => $code,
        //             'email' => $owner->email,
        //         ]);

        //         Notification::route('mail', $owner->email)
        //             ->notify(new SendEmailToOwnerAfterRegistrationNotification($code, $owner->email));
                
        //         Log::info('Email de vérification envoyé', ['email' => $owner->email]);
        //     } catch (\Exception $e) {
        //         Log::error('Erreur envoi email', [
        //             'error' => $e->getMessage(),
        //             'file' => $e->getFile(),
        //             'line' => $e->getLine()
        //         ]);
        //         // On continue malgré l'erreur d'email
        //     }
        // }

        DB::commit();

        return redirect()->route('owner.login')
            ->with('success', 'Votre compte a été crée avec succès, veuillez-vous connecter.');

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
   
}
