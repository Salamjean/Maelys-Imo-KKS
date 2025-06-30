<?php

namespace App\Http\Controllers\Proprietaire;
use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\Locataire;
use App\Models\ResetCodePasswordLocataire;
use App\Models\Visite;
use App\Notifications\SendEmailToLocataireAfterRegistrationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

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
            'attestation' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
                
            return redirect()->route('locataire.index.owner')->with('success', 'Locataire créé avec succès!');

        } catch (\Exception $e) {
            Log::error('Error creating locataire: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
        }
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
}
