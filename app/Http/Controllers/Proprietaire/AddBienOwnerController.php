<?php

namespace App\Http\Controllers\Proprietaire;
use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\Locataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PgSql\Lob;

class AddBienOwnerController extends Controller
{
    public function create()
    {
        return view('proprietaire.bien.create');
    }

    public function store(Request $request)
    {
        // Validation des données
        $validatedData = $request->validate([
            'type' => 'required|string',
            'utilisation' => 'required|string',
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
        ]);

        // Création d'une nouvelle instance de Bien
        $bien = new Bien();

        // Assignation des propriétés obligatoires
        $bien->type = $validatedData['type'];
        $bien->utilisation = $validatedData['utilisation'];
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
        $bien->proprietaire_id = Auth::guard('owner')->user()->code_id;
        $bien->save();

        return redirect()->route('owner.bienList')->with('success', 'Le bien a été enregistré avec succès!');
    }

     public function bienList()
    {
        // Vérifier si l'utilisateur est connecté en tant que propriétaire
        $proprietaireId = Auth::guard('owner')->user()->code_id;
        
        // Récupérer les biens du propriétaire connecté
        $biens = Bien::where('proprietaire_id', $proprietaireId)
                        ->where('status', 'Disponible')
                        ->paginate(6);
        
        return view('proprietaire.bien.index', compact('biens'));
    }

    public function bienListLoue()
    {
        // Vérifier si l'utilisateur est connecté en tant que propriétaire
        $proprietaireId = Auth::guard('owner')->user()->code_id;
        
        // Récupérer les biens du propriétaire connecté
        $biens = Bien::where('proprietaire_id', $proprietaireId)
                       ->where('status', 'Loué')
                        ->paginate(6);
        
        return view('proprietaire.bien.indexLoue', compact('biens'));
    }

    public function edit($id)
    {
        $bien = Bien::findOrFail($id);
        return view('proprietaire.bien.edit', compact('bien'));
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

            return redirect()->route('owner.bienList')->with('success', 'Le bien a été mis à jour avec succès!');

        } catch (\Exception $e) {
            Log::error('Error updating bien: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
        }
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
    
    return redirect()->route('owner.bienList')->with('success', 'Le bien a été republié avec succès et le statut du locataire a été mis à jour.');
}

public function getBiensDisponibles()
{
    $ownerId = Auth::guard('owner')->user()->code_id;
    $biens = Bien::where('proprietaire_id', $ownerId)
                ->where('status', 'Disponible')
                ->select('id', 'type', 'commune', 'prix')
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
}
