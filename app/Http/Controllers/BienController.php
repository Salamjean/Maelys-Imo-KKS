<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use App\Models\Bien;
use App\Models\Locataire;
use App\Models\Proprietaire;
use App\Models\Visite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BienController extends Controller
{
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
        $adminId = Auth::guard('admin')->user()->id;

        // Récupérer les biens qui répondent à l'une ou l'autre condition
        $biens = Bien::with('proprietaire')
                    ->whereNull('agence_id')
                    ->where(function($query) {
                        $query->whereNull('proprietaire_id') // 1er cas: bien sans propriétaire
                            ->orWhereHas('proprietaire', function($q) {
                                $q->where('gestion', 'agence'); // 2ème cas: bien avec propriétaire gestion agence
                            });
                    })
                    ->where('status', '!=', 'Loué')
                    ->paginate(4);

        return view('admin.bien.index', compact('biens', 'pendingVisits'));
    }
    public function indexAgence()
    {
        $agence_id = Auth::guard('agence')->user()->code_id;
        $agenceId = Auth::guard('agence')->user()->code_id;
        // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')
                        ->whereHas('bien', function ($query) use ($agenceId) {
                            $query->where('agence_id', $agenceId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        // Récupération des biens publier par l'agence
        $biens = Bien::with('proprietaire')
                    ->where('status','!=', 'Loué')
                    ->where('agence_id', $agence_id)
                    ->paginate(4);
        return view('agence.bien.index', compact('biens', 'pendingVisits'));
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
        // Récupération de l'agence connectée
        $adminId = Auth::guard('admin')->user()->id;
        $proprietaires = Proprietaire::whereNull('agence_id')
                                    ->where('gestion', 'agence')
                                ->get();
        return view('admin.bien.create', compact('proprietaires', 'pendingVisits'));
    }
    public function createAgence()
    {
        $agenceId = Auth::guard('agence')->user()->code_id;
        // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')
                        ->whereHas('bien', function ($query) use ($agenceId) {
                            $query->where('agence_id', $agenceId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        $agence_id = Auth::guard('agence')->user()->code_id;
        $proprietaires = Proprietaire::where('agence_id', $agence_id)
                                ->get();
        return view('agence.bien.create', compact('proprietaires', 'pendingVisits'));
    }

public function store(Request $request)
{
    
    // Validation des données
    $validatedData = $request->validate([
        'proprietaire_id' => 'nullable|exists:proprietaires,code_id',
        'type' => 'required|string',
        'autre_utilisation' => 'nullable|string',
        'utilisation' => 'required|string',
        'description' => 'required|string|max:255',
        'superficie' => 'required|string',
        'nombre_de_chambres' => 'nullable|string',
        'nombre_de_toilettes' => 'nullable|string',
        'garage' => 'nullable|string',
        'avance' => 'required|integer|min:1|max:99',
        'caution' => 'required|integer|min:1|max:99',
        'frais' => 'nullable|string',
        'montant_total' => 'nullable|string',
        'prix' => 'required|string',
        'commune' => 'required|string',
        'disponibilite' => 'required|string',
        'main_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        'additional_images1' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        'additional_images2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'additional_images3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'additional_images4' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'additional_images5' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
    ],[
        'type.required' => 'Le champ type est obligatoire',
        'utilisation.required' => 'Le champ utilisation est obligatoire',
        'description.required' => 'Le champ description est obligatoire',
        'description.max' => 'Le nombre maximum de lettre est de 255 lettres', 
        'superficie.required' => 'Le champ superficie est obligatoire',
        'prix.required' => 'Le champ prix est obligatoire',
        'commune.required' => 'Le champ commune est obligatoire',
        'disponibilite.required' => 'Le champ disponibilité est obligatoire',
        'main_image.required' => 'L\'image principale est obligatoire',
        'main_image.image' => 'L\'image principale doit être une image valide',
        'main_image.mimes' => 'L\'image principale doit être au format jpeg, png, jpg ou gif',
        'main_image.max' => 'L\'image principale ne doit pas dépasser 2 Mo',
        'additional_images1.required' => 'La première image supplémentaire est obligatoire',
        'additional_images1.image' => 'La première image supplémentaire doit être une image valide',
        'additional_images1.mimes' => 'La première image supplémentaire doit être au format jpeg, png, jpg ou gif',
        'additional_images1.max' => 'La première image supplémentaire ne doit pas dépasser 2 Mo',
        'additional_images2.image' => 'La deuxième image supplémentaire doit être une image valide',
        'proprietaire_id.exists' => 'Le propriétaire sélectionné est invalide'
    ]);

      // Génération du code unique en fonction du type
    $typePrefix = '';
    switch($validatedData['type']) {
        case 'Appartement':
            $typePrefix = 'AP';
            break;
        case 'Maison':
            $typePrefix = 'MA';
            break;
        case 'Bureau':
            $typePrefix = 'BU';
            break;
        default:
            $typePrefix = 'AG'; // Par défaut si aucun cas ne correspond
    }

    do {
        $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
        $numeroId = $typePrefix . $randomNumber;
    } while (Bien::where('numero_bien', $numeroId)->exists());

     // Si "Autre" est sélectionné et qu'une autre utilisation est spécifiée
    $utilisation = $validatedData['utilisation'];
    if ($utilisation === 'Autre' && !empty($validatedData['autre_utilisation'])) {
        $utilisation = $validatedData['autre_utilisation'];
    }

    // Création d'une nouvelle instance de Bien
    $bien = new Bien();

    // Assignation des propriétés obligatoires
    $bien->numero_bien = $numeroId;
    $bien->proprietaire_id = $validatedData['proprietaire_id'];
    $bien->type = $validatedData['type'];
    $bien->utilisation = $utilisation;
    $bien->description = $validatedData['description'];
    $bien->superficie = $validatedData['superficie'];
    $bien->prix = $validatedData['prix'];
    $bien->commune = $validatedData['commune'];
    $bien->date_fixe = $validatedData['disponibilite'];

    // Assignation des propriétés optionnelles
    $bien->nombre_de_chambres = $validatedData['nombre_de_chambres'] ?? null;
    $bien->nombre_de_toilettes = $validatedData['nombre_de_toilettes'] ?? null;
    $bien->garage = $validatedData['garage'] ?? null;
    $bien->avance = $validatedData['avance'] ?? null;
    $bien->caution = $validatedData['caution'] ?? null;
    $bien->frais = $validatedData['frais'] ?? null;
    $bien->montant_total = $validatedData['montant_total'] ?? null;

    // Gestion de l'image principale
    $mainImagePath = null;
    if ($request->hasFile('main_image')) {
        $mainImagePath = $request->file('main_image')->store('biens_images', 'public');
        $bien->image = $mainImagePath;
    }

    // Gestion des images supplémentaires selon votre format demandé
    $bien->image1 = null;
    if ($request->hasFile('additional_images1')) {
        $bien->image1 = $request->file('additional_images1')->store('biens_images', 'public');
    }
    
    $bien->image2 = null;
    if ($request->hasFile('additional_images2')) {
        $bien->image2 = $request->file('additional_images2')->store('biens_images', 'public');
    }
    
    $bien->image3 = null;
    if ($request->hasFile('additional_images3')) {
        $bien->image3 = $request->file('additional_images3')->store('biens_images', 'public');
    }
    
    $bien->image4 = null;
    if ($request->hasFile('additional_images4')) {
        $bien->image4 = $request->file('additional_images4')->store('biens_images', 'public');
    }
    
    $bien->image5 = null;
    if ($request->hasFile('additional_images5')) {
        $bien->image5 = $request->file('additional_images5')->store('biens_images', 'public');
    }
    
    $bien->save();

    return redirect()->route('bien.index')->with('success', 'Le bien a été enregistré avec succès!');
}
public function storeAgence(Request $request)
{
     $agence = Auth::guard('agence')->user();
    
    // Vérifier l'abonnement du propriétaire
    $abonnement = Abonnement::where('agence_id', $agence->code_id)
        ->where('statut', 'actif')
        ->latest()
        ->first();
    
    // Si l'abonnement est standard, vérifier le nombre de biens
    if ($abonnement && $abonnement->type === 'standard') {
        $nombreBiens = Bien::where('agence_id', $agence->code_id)->count();
        
        if ($nombreBiens >= 15) {
            return redirect()->back()
                ->with('error', 'Vous avez atteint la limite de 5 biens avec votre abonnement standard. Passez à un abonnement premium pour ajouter plus de biens.')
                ->withInput();
        }
    }
    
    // Si pas d'abonnement actif, refuser l'ajout
    if (!$abonnement) {
        return redirect()->back()
            ->with('error', 'Vous devez avoir un abonnement actif pour ajouter un bien.')
            ->withInput();
    }
    // Validation des données
    $validatedData = $request->validate([
        'proprietaire_id' => 'nullable|exists:proprietaires,code_id',
        'type' => 'required|string',
        'utilisation' => 'required|string',
        'autre_utilisation' => 'nullable|string',
        'description' => 'required|string',
        'superficie' => 'required|string',
        'nombre_de_chambres' => 'nullable|string',
        'nombre_de_toilettes' => 'nullable|string',
        'garage' => 'nullable|string',
        'avance' => 'required|integer|min:1|max:99',
        'caution' => 'required|integer|min:1|max:99',
        'frais' => 'nullable|string',
        'montant_total' => 'nullable|string',
        'prix' => 'required|string',
        'commune' => 'required|string',
        'disponibilite' => 'required|string',
        'main_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        'additional_images1' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        'additional_images2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'additional_images3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'additional_images4' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'additional_images5' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
   ], [
        'type.required' => 'Le champ type est obligatoire',
        'description.required' => 'Le champ description est obligatoire',
        'superficie.required' => 'Le champ superficie est obligatoire',
        'prix.required' => 'Le champ prix est obligatoire',
        'commune.required' => 'Le champ commune est obligatoire',
        'disponibilite.required' => 'Le champ disponibilité est obligatoire',
        'main_image.required' => 'L\'image principale est obligatoire',
        'main_image.image' => 'L\'image principale doit être une image valide',
        'main_image.mimes' => 'L\'image principale doit être au format jpeg, png, jpg ou gif',
        'main_image.max' => 'L\'image principale ne doit pas dépasser 2 Mo',
        'additional_images1.required' => 'La première image supplémentaire est obligatoire',
        'additional_images1.image' => 'La première image supplémentaire doit être une image valide',
        'additional_images1.mimes' => 'La première image supplémentaire doit être au format jpeg, png, jpg ou gif',
        'additional_images1.max' => 'La première image supplémentaire ne doit pas dépasser 2 Mo',
        'additional_images2.image' => 'La deuxième image supplémentaire doit être une image valide',
        'additional_images2.mimes' => 'La deuxième image supplémentaire doit être au format jpeg, png, jpg ou gif',
        'additional_images2.max' => 'La deuxième image supplémentaire ne doit pas dépasser 2 Mo',
        'additional_images3.image' => 'La troisième image supplémentaire doit être une image valide',
        'proprietaire_id.required' => 'Le champ propriétaire est obligatoire',
        'proprietaire_id.required_unless' => 'Le champ propriétaire est obligatoire',
        'proprietaire_id.exists' => 'Le propriétaire sélectionné est invalide'
    ]);

      // Génération du code unique en fonction du type
    $typePrefix = '';
    switch($validatedData['type']) {
        case 'Appartement':
            $typePrefix = 'AP';
            break;
        case 'Maison':
            $typePrefix = 'MA';
            break;
        case 'Bureau':
            $typePrefix = 'BU';
            break;
        default:
            $typePrefix = 'AG'; // Par défaut si aucun cas ne correspond
    }

    do {
        $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
        $numeroId = $typePrefix . $randomNumber;
    } while (Bien::where('numero_bien', $numeroId)->exists());
    
     // Si "Autre" est sélectionné et qu'une autre utilisation est spécifiée
    $utilisation = $validatedData['utilisation'];
    if ($utilisation === 'Autre' && !empty($validatedData['autre_utilisation'])) {
        $utilisation = $validatedData['autre_utilisation'];
    }
    
    // Création d'une nouvelle instance de Bien
    $bien = new Bien();

    // Assignation des propriétés obligatoires
    $bien->numero_bien = $numeroId;
    $bien->proprietaire_id = $validatedData['proprietaire_id'];
    $bien->type = $validatedData['type'];
    $bien->utilisation = $utilisation;
    $bien->description = $validatedData['description'];
    $bien->superficie = $validatedData['superficie'];
    $bien->prix = $validatedData['prix'];
    $bien->commune = $validatedData['commune'];
    $bien->date_fixe = $validatedData['disponibilite'];

    // Assignation des propriétés optionnelles
    $bien->nombre_de_chambres = $validatedData['nombre_de_chambres'] ?? null;
    $bien->nombre_de_toilettes = $validatedData['nombre_de_toilettes'] ?? null;
    $bien->garage = $validatedData['garage'] ?? null;
    $bien->avance = $validatedData['avance'] ?? null;
    $bien->caution = $validatedData['caution'] ?? null;
    $bien->frais = $validatedData['frais'] ?? null;
    $bien->montant_total = $validatedData['montant_total'] ?? null;

    // Gestion de l'image principale
    if ($request->hasFile('main_image')) {
        $mainImagePath = $request->file('main_image')->store('biens_images', 'public');
        $bien->image = $mainImagePath;
    }

    // Gestion des images supplémentaires
    if ($request->hasFile('additional_images1')) {
        $bien->image1 = $request->file('additional_images1')->store('biens_images', 'public');
    }
    
    if ($request->hasFile('additional_images2')) {
        $bien->image2 = $request->file('additional_images2')->store('biens_images', 'public');
    }
    
    if ($request->hasFile('additional_images3')) {
        $bien->image3 = $request->file('additional_images3')->store('biens_images', 'public');
    }
    
    if ($request->hasFile('additional_images4')) {
        $bien->image4 = $request->file('additional_images4')->store('biens_images', 'public');
    }
    
    if ($request->hasFile('additional_images5')) {
        $bien->image5 = $request->file('additional_images5')->store('biens_images', 'public');
    }

    $bien->agence_id = Auth::guard('agence')->user()->code_id;
    $bien->save();

    return redirect()->route('bien.index.agence')->with('success', 'Le bien a été enregistré avec succès!');
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
    $bien = Bien::findOrFail($id);
     $proprietaires = Proprietaire::whereNull('agence_id')
                                    ->where('gestion', 'agence')
                                ->get();
    return view('admin.bien.edit', compact('bien','proprietaires', 'pendingVisits'));
}
public function editAgence($id)
{
    $bien = Bien::findOrFail($id);
    $agence_id = Auth::guard('agence')->user()->code_id;
    $agenceId = Auth::guard('agence')->user()->code_id;
        // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')
                        ->whereHas('bien', function ($query) use ($agenceId) {
                            $query->where('agence_id', $agenceId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        $proprietaires = Proprietaire::where('agence_id', $agence_id)
                                ->get();
    return view('agence.bien.edit', compact('bien', 'pendingVisits','proprietaires'));
}

public function update(Request $request, $id)
{
    $bien = Bien::findOrFail($id);

    // Validation des données
    $validatedData = $request->validate([
        'proprietaire_id' => 'nullable|exists:proprietaires,code_id',
        'type' => 'required|string',
        'utilisation' => 'required|string',
        'autre_utilisation' => 'nullable|string',
        'description' => 'required|string',
        'superficie' => 'required|string',
        'nombre_de_chambres' => 'nullable|string',
        'nombre_de_toilettes' => 'nullable|string',
        'garage' => 'nullable|string',
        'avance' => 'required|integer|min:1|max:99',
        'caution' => 'required|integer|min:1|max:99',
        'frais' => 'nullable|string',
        'montant_total' => 'nullable|string',
        'prix' => 'required|string',
        'commune' => 'required|string',
        'disponibilite' => 'required|string',
        'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'additional_images1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'additional_images2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'additional_images3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'additional_images4' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'additional_images5' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
    ]);

    try {
         // Si "Autre" est sélectionné et qu'une autre utilisation est spécifiée
        $utilisation = $validatedData['utilisation'];
        if ($utilisation === 'Autre' && !empty($validatedData['autre_utilisation'])) {
            $utilisation = $validatedData['autre_utilisation'];
        }
        // Mise à jour des propriétés obligatoires
        $bien->proprietaire_id = $validatedData['proprietaire_id'];
        $bien->type = $validatedData['type'];
        $bien->utilisation = $utilisation;
        $bien->description = $validatedData['description'];
        $bien->superficie = $validatedData['superficie'];
        $bien->prix = $validatedData['prix'];
        $bien->commune = $validatedData['commune'];
        $bien->date_fixe = $validatedData['disponibilite'];

        // Mise à jour des propriétés optionnelles
        $bien->nombre_de_chambres = $validatedData['nombre_de_chambres'] ?? null;
        $bien->nombre_de_toilettes = $validatedData['nombre_de_toilettes'] ?? null;
        $bien->garage = $validatedData['garage'] ?? null;
        $bien->avance = $validatedData['avance'] ?? null;
        $bien->caution = $validatedData['caution'] ?? null;
        $bien->frais = $validatedData['frais'] ?? null;
        $bien->montant_total = $validatedData['montant_total'] ?? null;

        // Gestion de l'image principale
        if ($request->hasFile('main_image')) {
            // Supprimer l'ancienne image si elle existe
            if ($bien->image) {
                Storage::disk('public')->delete($bien->image);
            }
            $mainImagePath = $request->file('main_image')->store('biens_images', 'public');
            $bien->image = $mainImagePath;
        }

        // Gestion des images supplémentaires
        $imageFields = ['image1', 'image2', 'image3', 'image4', 'image5'];
        for ($i = 1; $i <= 5; $i++) {
            $fieldName = 'additional_images' . $i;
            if ($request->hasFile($fieldName)) {
                // Supprimer l'ancienne image si elle existe
                if ($bien->{$imageFields[$i-1]}) {
                    Storage::disk('public')->delete($bien->{$imageFields[$i-1]});
                }
                $imagePath = $request->file($fieldName)->store('biens_images', 'public');
                $bien->{$imageFields[$i-1]} = $imagePath;
            }
        }

        $bien->save();

        return redirect()->route('bien.index')->with('success', 'Le bien a été mis à jour avec succès!');

    } catch (\Exception $e) {
        Log::error('Error updating bien: ' . $e->getMessage());
        return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
    }
}
public function updateAgence(Request $request, $id)
{
    $bien = Bien::findOrFail($id);

    // Validation des données
    $validatedData = $request->validate([
        'proprietaire_id' => 'required|exists:proprietaires,code_id',
        'type' => 'required|string|in:Appartement,Maison,Bureau,Terrain',
        'description' => 'required|string|max:2000',
        'autre_utilisation' => 'nullable|string',
        'superficie' => 'required|numeric|min:1',
        'nombre_de_chambres' => 'nullable|integer|min:0',
        'nombre_de_toilettes' => 'nullable|integer|min:0',
        'garage' => 'nullable|string|in:Oui,Non',
        'avance' => 'required|integer|min:1|max:99',
        'caution' => 'required|integer|min:1|max:99',
        'frais' => 'required|integer|min:1',
        'montant_total' => 'required|numeric|min:0',
        'prix' => 'required|numeric|min:0',
        'commune' => 'required|string|max:255',
        'disponibilite' => 'required|integer|min:1|max:31',
        'utilisation' => 'required|string|in:Habitation,Bureau,Autre',
        'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'additional_images1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'additional_images2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'additional_images3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'additional_images4' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'additional_images5' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
    ]);

     // Si "Autre" est sélectionné et qu'une autre utilisation est spécifiée
    $utilisation = $validatedData['utilisation'];
    if ($utilisation === 'Autre' && !empty($validatedData['autre_utilisation'])) {
        $utilisation = $validatedData['autre_utilisation'];
    }

    DB::beginTransaction();
    try {
        // Mise à jour des informations de base
        $bien->fill([
            'proprietaire_id' => $validatedData['proprietaire_id'],
            'type' => $validatedData['type'],
            'description' => $validatedData['description'],
            'superficie' => $validatedData['superficie'],
            'nombre_de_chambres' => $validatedData['nombre_de_chambres'] ?? null,
            'nombre_de_toilettes' => $validatedData['nombre_de_toilettes'] ?? null,
            'garage' => $validatedData['garage'] ?? null,
            'prix' => $validatedData['prix'],
            'commune' => $validatedData['commune'],
            'date_fixe' => $validatedData['disponibilite'],
            'avance' => $validatedData['avance'],
            'caution' => $validatedData['caution'],
            'frais' => $validatedData['frais'],
            'montant_total' => $validatedData['montant_total'],
            'utilisation' => $utilisation,
        ]);

        // Gestion de l'image principale
        if ($request->hasFile('main_image')) {
            // Supprimer l'ancienne image si elle existe
            if ($bien->image) {
                Storage::disk('public')->delete($bien->image);
            }
            $bien->image = $request->file('main_image')->store('biens_images', 'public');
        } elseif ($request->has('delete_main_image')) {
            // Suppression demandée via le bouton
            Storage::disk('public')->delete($bien->image);
            $bien->image = null;
        }

        // Gestion des images supplémentaires
        $imageFields = ['image1', 'image2', 'image3', 'image4', 'image5'];
        for ($i = 1; $i <= 5; $i++) {
            $fieldName = 'additional_images' . $i;
            $deleteField = 'delete_image' . $i;

            if ($request->hasFile($fieldName)) {
                // Supprimer l'ancienne image si elle existe
                if ($bien->{$imageFields[$i-1]}) {
                    Storage::disk('public')->delete($bien->{$imageFields[$i-1]});
                }
                $bien->{$imageFields[$i-1]} = $request->file($fieldName)->store('biens_images', 'public');
            } elseif ($request->has($deleteField)) {
                // Suppression demandée via le bouton
                Storage::disk('public')->delete($bien->{$imageFields[$i-1]});
                $bien->{$imageFields[$i-1]} = null;
            }
        }

        $bien->save();
        DB::commit();

        return redirect()->route('bien.index.agence')->with('success', 'Le bien a été mis à jour avec succès!');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Erreur lors de la mise à jour du bien: ' . $e->getMessage());
        return back()->with('error', 'Une erreur est survenue lors de la mise à jour: ' . $e->getMessage())->withInput();
    }
}

    //Les methodes pour les pages de biens

    public function appartements(Request $request)
    {
        // Initialisation de la requête pour les appartements disponibles
        $query = Bien::where('status', 'Disponible')
                    ->where('type', 'Appartement');
        
        // Application des filtres
        if ($request->has('commune') && $request->commune != '') {
            $query->where('commune', 'like', '%'.$request->commune.'%');
        }
        
        if ($request->has('prix_max') && $request->prix_max != '') {
            $query->where('prix', '<=', $request->prix_max);
        }
        
        // Récupération des résultats
        $biens = $query->get();
        
        return view('home.pages.appartements', compact('biens'));
    }
    public function maisons(Request $request)
    {
        // Initialisation de la requête avec eager loading
        $query = Bien::with(['proprietaire', 'agence'])
                    ->where('status', 'Disponible')
                    ->where('type', 'Maison');
        
        // Application des filtres
        if ($request->has('commune') && $request->commune != '') {
            $query->where('commune', 'like', '%'.$request->commune.'%');
        }
        
        if ($request->has('prix_max') && $request->prix_max != '') {
            $query->where('prix', '<=', $request->prix_max);
        }
        
        // Récupération des résultats
        $biens = $query->get();
        
        return view('home.pages.maisons', compact('biens'));
    }
    public function terrains(Request $request)
    {
        // Initialisation de la requête pour les appartements disponibles
        $query = Bien::where('status', 'Disponible')
                    ->where('type', 'Bureau');
        
        // Application des filtres
        if ($request->has('commune') && $request->commune != '') {
            $query->where('commune', 'like', '%'.$request->commune.'%');
        }
        
        if ($request->has('prix_max') && $request->prix_max != '') {
            $query->where('prix', '<=', $request->prix_max);
        }
        
        // Récupération des résultats
        $biens = $query->get();
        return view('home.pages.terrains', compact('biens'));
    }

    public function visiter($id)
{
    $bien = Bien::findOrFail($id);
    return view('home.visite', compact('bien'));
}

public function rented(){
    // Récupération de l'agence connectée
    $agence_id = Auth::guard('agence')->user()->code_id;
    $agenceId = Auth::guard('agence')->user()->code_id;
        // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')
                        ->whereHas('bien', function ($query) use ($agenceId) {
                            $query->where('agence_id', $agenceId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
    // Récupération des biens loués
    $biens = Bien::with('proprietaire')
            ->where('status', 'Loué')
            ->where('agence_id', $agence_id)
            ->paginate();
    return view('agence.bien.rented', compact('biens', 'pendingVisits'));
}

public function rentedAdmin(){
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
    // Vérification si l'utilisateur est connecté en tant qu'agence
        $adminId = Auth::guard('admin')->user()->id;
    // Récupération des biens loués
    $biens = Bien::whereNull('agence_id')
                ->whereNull('proprietaire_id')
                ->where('status', 'Loué') ->paginate();
    return view('admin.bien.rented', compact('biens', 'pendingVisits'));
}

public function destroy($id)
{
    try {
        $bien = Bien::findOrFail($id);
        
        // Vérifier si le bien est loué
        if ($bien->status === 'Loué') {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer un bien loué.'
            ], 400);
        }

        // Supprimer les images associées
        $imageFields = ['image', 'image1', 'image2', 'image3', 'image4', 'image5'];
        foreach ($imageFields as $field) {
            if ($bien->$field) {
                Storage::disk('public')->delete($bien->$field);
            }
        }

        $bien->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bien supprimé avec succès.'
        ]);

    } catch (\Exception $e) {
        Log::error('Error deleting bien: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Une erreur est survenue lors de la suppression.'
        ], 500);
    }
}
public function destroyAgence($id)
{
    try {
        $bien = Bien::findOrFail($id);
        
        // Vérifier si le bien est loué
        if ($bien->status === 'Loué') {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer un bien loué.'
            ], 400);
        }

        // Supprimer les images associées
        $imageFields = ['image', 'image1', 'image2', 'image3', 'image4', 'image5'];
        foreach ($imageFields as $field) {
            if ($bien->$field) {
                Storage::disk('public')->delete($bien->$field);
            }
        }

        $bien->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bien supprimé avec succès.'
        ]);

    } catch (\Exception $e) {
        Log::error('Error deleting bien: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Une erreur est survenue lors de la suppression.'
        ], 500);
    }
}

public function republier(Bien $bien)
{
    // Mettre à jour le statut du bien
    $bien->status = 'Disponible';
    $bien->save();
    
    // Mettre à jour le statut du locataire si le bien avait un locataire
    if ($bien->locataire) {
        $locataire = $bien->locataire;
        $locataire->status = request('locataire_status');
        $locataire->motif = request('locataire_motif', null);
        $locataire->bien_id = null; // Détacher le bien du locataire
        $locataire->save();
    }
    
    return redirect()->route('bien.index')->with('success', 'Le bien a été republié avec succès et le statut du locataire a été mis à jour.');
}

public function getBiensDisponibles()
{
    $biens =  Bien::whereNull('agence_id')
                ->where('status', 'Disponible')
                ->select('id', 'type', 'commune', 'prix')
                ->whereNull('proprietaire_id') // 1er cas: bien sans propriétaire
                            ->orWhereHas('proprietaire', function($q) {
                                $q->where('gestion', 'agence'); // 2ème cas: bien avec propriétaire gestion agence
                            })
                            ->get();
    
    return response()->json($biens);
}
public function attribuerBien(Request $request, Locataire $locataire)
{
    $request->validate([
        'bien_id' => 'required|exists:biens,id',
    ]);

    // Vérifier que le bien est disponible
    $bien = Bien::find($request->bien_id);
    if ($bien->status !== 'Disponible') {
        return response()->json(['error' => 'Le bien sélectionné n\'est pas disponible'], 400);
    }

    // Mettre à jour le locataire
    $locataire->bien_id = $request->bien_id;
    $locataire->status = 'Actif';
    $locataire->motif = null;
    $locataire->save();

    // Mettre à jour le statut du bien
    $bien->status = 'Loué';
    $bien->save();

    return response()->json(['success' => 'Bien attribué avec succès au locataire']);
}
public function getBiensDisponiblesAgence()
{
    $agenceId = Auth::guard('agence')->user()->code_id;
    $biens =  Bien::where('agence_id', $agenceId)
                ->where('status', 'Disponible')
                ->select('id', 'type', 'commune', 'prix')
                ->get();
    
    return response()->json($biens);
}
public function attribuerBienAgence(Request $request, Locataire $locataire)
{
    $request->validate([
        'bien_id' => 'required|exists:biens,id',
    ]);

    // Vérifier que le bien est disponible
    $bien = Bien::find($request->bien_id);
    if ($bien->status !== 'Disponible') {
        return response()->json(['error' => 'Le bien sélectionné n\'est pas disponible'], 400);
    }

    // Mettre à jour le locataire
    $locataire->bien_id = $request->bien_id;
    $locataire->status = 'Actif';
    $locataire->motif = null;
    $locataire->save();

    // Mettre à jour le statut du bien
    $bien->status = 'Loué';
    $bien->save();

    return response()->json(['success' => 'Bien attribué avec succès au locataire']);
}
public function republierAgence(Bien $bien)
{
    // Mettre à jour le statut du bien
    $bien->status = 'Disponible';
    $bien->save();
    
    // Mettre à jour le statut du locataire si le bien avait un locataire
    if ($bien->locataire) {
        $locataire = $bien->locataire;
        $locataire->status = request('locataire_status');
        $locataire->motif = request('locataire_motif', null);
        $locataire->bien_id = null; // Détacher le bien du locataire
        $locataire->save();
    }
    
    return redirect()->route('bien.index.agence')->with('success', 'Le bien a été republié avec succès et le statut du locataire a été mis à jour.');
}
}
