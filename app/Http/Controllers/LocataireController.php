<?php

namespace App\Http\Controllers;

use App\Models\Bien;
use App\Models\Locataire;
use App\Models\ResetCodePasswordLocataire;
use App\Notifications\SendEmailToLocataireAfterRegistrationNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactAgencyMail;

class LocataireController extends Controller
{
    public function dashboard()
    {
        // Récupérer le locataire avec son bien associé
        $locataire = Locataire::with(['bien','agence'])->findOrFail(Auth::guard('locataire')->user()->id);
        return view('locataire.dashboard',compact('locataire'));
    }
        public function index()
    {
        // Récupération des locataires avec les relations nécessaires
        $locataires = Locataire::with(['bien', 'paiements' => function($query) {
            $query->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        }])
        ->where('status', '!=', 'Pas sérieux')
        ->where('agence_id', Auth::guard('agence')->user()->id)
        ->paginate(6);

        // Ajout d'une propriété à chaque locataire pour déterminer si le bouton doit être affiché
        $locataires->getCollection()->transform(function($locataire) {
            $today = now()->format('d');
            $currentMonthPaid = $locataire->paiements->isNotEmpty();
            $locataire->show_reminder_button = ($locataire->bien->date_fixe == $today) && !$currentMonthPaid;
            return $locataire;
        });

        return view('agence.locataire.index', compact('locataires'));
    }
    public function indexAdmin(){
        // Récupération de tous les locataires
        $locataires = Locataire::where('status', '!=', 'Pas sérieux')
                    ->whereNull('agence_id')
                    ->paginate(6);
        return view('admin.locataire.index',compact('locataires'));
    }
    public function indexSerieux(){
        // Récupération de tous les locataires
        $locataires = Locataire::where('status', 'Pas sérieux')
                    ->paginate(6);
        return view('agence.locataire.indexSerieux',compact('locataires'));
    }
    public function indexSerieuxAdmin(){
        // Récupération de tous les locataires
        $locataires = Locataire::where('status', 'Pas sérieux')
                    ->paginate(6);
        return view('admin.locataire.indexSerieux',compact('locataires'));
    }
    public function create()
    {
        // Récupérer les biens disponibles de l'agence
        $agenceId = Auth::guard('agence')->user()->id;
        $biens = Bien::where('agence_id', $agenceId)
                    ->where('status', 'Disponible')
                    ->get();
        
        return view('agence.locataire.create', compact('biens'));
    }
    public function createAdmin()
    {
        $biens = Bien::whereNull('agence_id')
                ->where('status', 'Disponible')->get();
        
        return view('admin.locataire.create', compact('biens'));
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
            'attestation' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'contrat' => 'required|file|mimes:jpeg,png,jpg,gif,pdf|max:2048',
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
            'attestation.required' => 'L\'attestation de travail est obligatoire.',
            'attestation.image' => 'L\'attestation de travail doit être une image.',
            'attestation.mimes' => 'L\'attestation de travail doit être de type: jpeg, png, jpg ou gif.',
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

            // Création du locataire
            $locataire = new Locataire();
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
            $locataire->agence_id = Auth::guard('agence')->user()->id;
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

            // Envoi de l'e-mail de vérification
            $agence = Auth::guard('agence')->user();
            ResetCodePasswordLocataire::where('email', $locataire->email)->delete();
            $code = rand(1000, 4000);
            ResetCodePasswordLocataire::create([
                'code' => $code,
                'email' => $locataire->email,
            ]);

            Notification::route('mail', $locataire->email)
                ->notify(new SendEmailToLocataireAfterRegistrationNotification($code, $locataire->email, $agence->name));
                
            return redirect()->route('locataire.index')->with('success', 'Locataire créé avec succès!');

        } catch (\Exception $e) {
            Log::error('Error creating locataire: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        $locataire = Locataire::findOrFail($id);
        // Récupérer les biens disponibles de l'agence
        $agenceId = Auth::guard('agence')->user()->id;
        $biens = Bien::where('status', '!=', 'Loué')
                ->where('agence_id', $agenceId)
                ->orWhere('id', $locataire->bien_id)
                ->get();
        
        return view('agence.locataire.edit', compact('locataire', 'biens'));
    }

    public function editAdmin($id)
    {
        $locataire = Locataire::findOrFail($id);
        $biens = Bien::where('status', '!=', 'Loué')
                ->whereNull('agence_id')
                ->orWhere('id', $locataire->bien_id)
                ->get();
        
        return view('admin.locataire.edit', compact('locataire', 'biens'));
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

        return redirect()->route('locataire.index')->with('success', 'Locataire mis à jour avec succès!');

    } catch (\Exception $e) {
        Log::error('Error updating locataire: ' . $e->getMessage());
        return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
    }
}
    public function updateAdmin(Request $request, $id)
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

            return redirect()->route('locataire.admin.index')->with('success', 'Locataire mis à jour avec succès!');

        } catch (\Exception $e) {
            Log::error('Error updating locataire: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
        }
    }
    public function storeAdmin(Request $request)
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
            'attestation' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'contrat' => 'required|file|mimes:jpeg,png,jpg,gif,pdf|max:2048',
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
            'attestation.required' => 'L\'attestation de travail est obligatoire.',
            'attestation.image' => 'L\'attestation de travail doit être une image.',
            'attestation.mimes' => 'L\'attestation de travail doit être de type: jpeg, png, jpg ou gif.',
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

            // Création du locataire
            $locataire = new Locataire();
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

            // Envoi de l'e-mail de vérification
            $agence = Auth::guard('admin')->user();
            ResetCodePasswordLocataire::where('email', $locataire->email)->delete();
            $code = rand(1000, 4000);
            ResetCodePasswordLocataire::create([
                'code' => $code,
                'email' => $locataire->email,
            ]);

            Notification::route('mail', $locataire->email)
                ->notify(new SendEmailToLocataireAfterRegistrationNotification($code, $locataire->email, $agence->name));
                
            return redirect()->route('locataire.admin.index')->with('success', 'Locataire créé avec succès!');
    
        } catch (\Exception $e) {
            Log::error('Error creating locataire: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
        }
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
public function updateStatusAdmin(Request $request, Locataire $locataire)
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

public function defineAccess($email){
    //Vérification si le sous-admin existe déjà
    $checkSousadminExiste = Locataire::where('email', $email)->first();
    if($checkSousadminExiste){
        return view('agence.locataire.auth.validate', compact('email'));
    }else{
        return redirect()->route('locataire.login')->with('error', 'Email inconnu');
    };
}

public function submitDefineAccess(Request $request)
    {
        // Validation des données
        $request->validate([
            'code' => 'required|exists:reset_code_password_locataires,code',
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
            $locataire = Locataire::where('email', $request->email)->first();
    
            if ($locataire) {
                // Mise à jour du mot de passe
                $locataire->password = Hash::make($request->password);
                $locataire->update();
    
                if ($locataire) {
                    $existingcodelocataire = ResetCodePasswordLocataire::where('email', $locataire->email)->count();
    
                    if ($existingcodelocataire > 1) {
                        ResetCodePasswordLocataire::where('email', $locataire->email)->delete();
                    }
                }
    
                return redirect()->route('locataire.login')->with('success', 'Compte mis à jour avec succès');
            } else {
                return redirect()->route('locataire.login')->with('error', 'Email inconnu');
            }
        } catch (\Exception $e) {
            Log::error('Error updating admin profile: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue : ' . $e->getMessage())->withInput();
        }
    }

    public function login(){
        return view('agence.locataire.auth.login');
     }

     public function authenticate(Request $request)
{
    // Validation des champs du formulaire
    $request->validate([
        'email' => 'required|exists:locataires,email',
        'password' => 'required|min:8',
    ], [
        'email.required' => 'Le mail est obligatoire.',
        'email.exists' => 'Cette adresse mail n\'existe pas.',
        'password.required' => 'Le mot de passe est obligatoire.',
        'password.min' => 'Le mot de passe doit avoir au moins 8 caractères.',
    ]);

    try {
        // Récupérer le locataire avant tentative de connexion
        $locataire = Locataire::where('email', $request->email)->first();

        // Vérifier le statut du locataire
        if ($locataire && in_array($locataire->status, ['Inactif', 'Pas sérieux'])) {
            return back()->withInput()->with('account_error', [
                'title' => 'Compte désactivé',
                'message' => 'Votre compte est désactivé. Veuillez contacter votre agence',
                'status' => $locataire->status
            ]);
        }

        // Tentative d'authentification
        if(auth('locataire')->attempt($request->only('email', 'password'))) {
            return redirect()->route('locataire.dashboard')->with('success', 'Bienvenue sur votre page');
        } else {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->with('error', 'Mot de passe incorrect.');
        }
    } catch (Exception $e) {
        // En production, vous devriez logger l'erreur plutôt que de la dd()
        return redirect()->back()
            ->withInput($request->only('email'))
            ->with('error', 'Une erreur est survenue lors de la connexion.');
    }
}

     public function logout(){
        auth('locataire')->logout();
        return redirect()->route('locataire.login')->with('success', 'Déconnexion réussie.');
    }

    public function show($id)
    {
        // Récupérer le locataire avec son bien associé
        $locataire = Locataire::with(['bien','agence'])->findOrFail($id);
        
        // Vérifier si le bien existe
        if(!$locataire->bien) {
            return redirect()->back()->with('error', 'Aucun bien associé à ce locataire');
        }
        
        return view('locataire.show', compact('locataire'));
    }


    //Profil
    public function editProfile()
    {
        $locataire = Auth::guard('locataire')->user();
        return view('locataire.auth.profile', compact('locataire'));
    }

    public function updateProfile(Request $request)
    {
        $locataire = Auth::guard('locataire')->user();
    
        $rules = [
            'name' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:locataires,email,'.$locataire->id,
            'contact' => 'required|string|min:10',
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
                if ($locataire->profile_image) {
                    Storage::disk('public')->delete($locataire->profile_image);
                }
                $locataire->profile_image = $request->file('profile_image')->store('profile_images', 'public');
            }
    
            // Mise à jour des informations de base
            $locataire->name = $request->name;
            $locataire->prenom = $request->prenom;
            $locataire->email = $request->email;
            $locataire->contact = $request->contact;
    
            // Mise à jour du mot de passe seulement si fourni
            if ($request->filled('password')) {
                $locataire->password = Hash::make($request->password);
            }
    
            $locataire->save();
    
            return redirect()->route('locataire.dashboard')->with('success', 'Vos informations on bien été mis à jour!');
    
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour profil agence: '.$e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la mise à jour');
        }
    }

    public function sendEmailToAgency(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'agency_email' => 'required|email'
        ]);

        try {
            $user = Auth::guard('locataire')->user();
            
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            Mail::to($validated['agency_email'])->send(new ContactAgencyMail(
                $validated['subject'],
                $validated['content'],
                $user->name,
                $user->email
            ));

            return response()->json(['success' => true]);
            
        } catch (Exception $e) {
            Log::error('Email error: '.$e->getMessage()."\n".$e->getTraceAsString());
            return response()->json([
                'error' => 'Erreur lors de l\'envoi du message',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
