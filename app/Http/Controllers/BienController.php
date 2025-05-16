<?php

namespace App\Http\Controllers;

use App\Models\Bien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BienController extends Controller
{
    public function index()
    {
        // Récupération de tous les biens
        $biens = Bien::whereNull('agence_id')
                    ->where('status','!=', 'Loué')
                    ->paginate(4);
        return view('admin.bien.index', compact('biens'));
    }
    public function indexAgence()
    {
        $agence_id = Auth::guard('agence')->user()->id;
        // Récupération des biens publier par l'agence
        $biens = Bien::where('status','!=', 'Loué')
                    ->where('agence_id', $agence_id)
                    ->paginate(4);
        return view('agence.bien.index', compact('biens'));
    }

    public function create()
    {
        return view('admin.bien.create');
    }
    public function createAgence()
    {
        return view('agence.bien.create');
    }

public function store(Request $request)
{
    // Validation des données
    $validatedData = $request->validate([
        'type' => 'required|string',
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
    ]);

    // Création d'une nouvelle instance de Bien
    $bien = new Bien();

    // Assignation des propriétés obligatoires
    $bien->type = $validatedData['type'];
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

    $bien->save();

    return redirect()->route('bien.index')->with('success', 'Le bien a été enregistré avec succès!');
}
public function storeAgence(Request $request)
{
    // Validation des données
    $validatedData = $request->validate([
        'type' => 'required|string',
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
    ]);

    // Création d'une nouvelle instance de Bien
    $bien = new Bien();

    // Assignation des propriétés obligatoires
    $bien->type = $validatedData['type'];
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

    $bien->agence_id = Auth::guard('agence')->user()->id;
    $bien->save();

    return redirect()->route('bien.index.agence')->with('success', 'Le bien a été enregistré avec succès!');
}

public function edit($id)
{
    $bien = Bien::findOrFail($id);
    return view('admin.bien.edit', compact('bien'));
}
public function editAgence($id)
{
    $bien = Bien::findOrFail($id);
    return view('agence.bien.edit', compact('bien'));
}

public function update(Request $request, $id)
{
    $bien = Bien::findOrFail($id);

    // Validation des données
    $validatedData = $request->validate([
        'type' => 'required|string',
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
        // Mise à jour des informations de base
        $bien->type = $validatedData['type'];
        $bien->description = $validatedData['description'];
        $bien->superficie = $validatedData['superficie'];
        $bien->prix = $validatedData['prix'];
        $bien->commune = $validatedData['commune'];
        $bien->date_fixe = $validatedData['disponibilite'];
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
        'type' => 'required|string',
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
        // Mise à jour des informations de base
        $bien->type = $validatedData['type'];
        $bien->description = $validatedData['description'];
        $bien->superficie = $validatedData['superficie'];
        $bien->prix = $validatedData['prix'];
        $bien->commune = $validatedData['commune'];
        $bien->date_fixe = $validatedData['disponibilite'];
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

        return redirect()->route('bien.index.agence')->with('success', 'Le bien a été mis à jour avec succès!');

    } catch (\Exception $e) {
        Log::error('Error updating bien: ' . $e->getMessage());
        return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
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
       // Initialisation de la requête pour les appartements disponibles
        $query = Bien::where('status', 'Disponible')
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
    $agence_id = Auth::guard('agence')->user()->id;
    // Récupération des biens loués
    $biens = Bien::where('status', 'Loué')
            ->where('agence_id', $agence_id)
            ->paginate();
    return view('agence.bien.rented', compact('biens'));
}

public function rentedAdmin(){
    // Récupération des biens loués
    $biens = Bien::whereNull('agence_id')
                ->where('status', 'Loué') ->paginate();
    return view('admin.bien.rented', compact('biens'));
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
