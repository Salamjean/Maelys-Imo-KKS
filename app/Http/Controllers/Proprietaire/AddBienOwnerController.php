<?php

namespace App\Http\Controllers\Proprietaire;
use App\Http\Controllers\Controller;
use App\Models\Abonnement;
use App\Models\Bien;
use App\Models\Locataire;
use App\Models\Visite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PgSql\Lob;

class AddBienOwnerController extends Controller
{
    public function create()
    {
        $ownerId = Auth::guard('owner')->user()->code_id;
     // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')->where('statut', '!=', 'effectuée')
                        ->where('statut', '!=', 'annulée')
                        ->whereHas('bien', function ($query) use ($ownerId) {
                             $query->where('proprietaire_id', $ownerId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        return view('proprietaire.bien.create', compact('pendingVisits'));
    }

    public function store(Request $request)
{
    // Récupérer le propriétaire connecté
    $proprietaire = Auth::guard('owner')->user();
    
    // Vérifier l'abonnement du propriétaire
    $abonnement = Abonnement::where('proprietaire_id', $proprietaire->code_id)
        ->where('statut', 'actif')
        ->latest()
        ->first();
    
    // Si l'abonnement est standard, vérifier le nombre de biens
    if ($abonnement && $abonnement->type === 'standard') {
        $nombreBiens = Bien::where('proprietaire_id', $proprietaire->code_id)->count();
        
        if ($nombreBiens >= 5) {
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

    // Le reste de votre méthode store originale...
    $validatedData = $request->validate([
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
    $bien->proprietaire_id = $proprietaire->code_id;
    $bien->save();

    return redirect()->route('owner.bienList')->with('success', 'Le bien a été enregistré avec succès!');
}

    public function bienList()
    {
        // Récupérer l'ID du propriétaire connecté
        $proprietaire = Auth::guard('owner')->user();
        $proprietaireId = $proprietaire->code_id;

        // Compter les demandes de visite en attente
        $pendingVisits = Visite::where('statut', 'en attente')
                            ->where('statut', '!=', 'effectuée')
                            ->where('statut', '!=', 'annulée')
                            ->whereHas('bien', function ($query) use ($proprietaireId) {
                                $query->where('proprietaire_id', $proprietaireId);
                            })
                            ->count();

        // Récupérer l'abonnement actif du propriétaire
        $abonnement = Abonnement::where('proprietaire_id', $proprietaireId)
                            ->where('statut', 'actif')
                            ->latest()
                            ->first();

        // Récupérer les biens selon le type d'abonnement
        if ($abonnement && $abonnement->type === 'standard') {
            // Pour standard: 5 derniers biens paginés
            $biens = Bien::where('proprietaire_id', $proprietaireId)
                        ->where('status', 'Disponible')
                        ->orderBy('created_at', 'desc')
                        ->paginate(5);
        } else {
            // Pour premium ou sans abonnement: tous les biens paginés
            $biens = Bien::where('proprietaire_id', $proprietaireId)
                        ->where('status', 'Disponible')
                        ->orderBy('created_at', 'desc')
                        ->paginate(6);
        }

        return view('proprietaire.bien.index', compact('biens', 'pendingVisits', 'abonnement'));
    }

    

    public function bienListLoue()
    {
          $ownerId = Auth::guard('owner')->user()->code_id;
     // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')->where('statut', '!=', 'effectuée')
                        ->where('statut', '!=', 'annulée')
                        ->whereHas('bien', function ($query) use ($ownerId) {
                             $query->where('proprietaire_id', $ownerId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        // Vérifier si l'utilisateur est connecté en tant que propriétaire
        $proprietaireId = Auth::guard('owner')->user()->code_id;
        
        // Récupérer les biens du propriétaire connecté
        $biens = Bien::where('proprietaire_id', $proprietaireId)
                       ->where('status', 'Loué')
                        ->paginate(6);
        
        return view('proprietaire.bien.indexLoue', compact('biens', 'pendingVisits'));
    }

    public function edit($id)
    {
        $ownerId = Auth::guard('owner')->user()->code_id;
     // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')->where('statut', '!=', 'effectuée')
                        ->where('statut', '!=', 'annulée')
                        ->whereHas('bien', function ($query) use ($ownerId) {
                             $query->where('proprietaire_id', $ownerId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        $bien = Bien::findOrFail($id);
        return view('proprietaire.bien.edit', compact('bien', 'pendingVisits'));
    }

    public function update(Request $request, $id)
{
    $bien = Bien::findOrFail($id);
    
    // Vérifier que le bien appartient au propriétaire connecté
    $proprietaire = Auth::guard('owner')->user();
    if ($bien->proprietaire_id !== $proprietaire->code_id) {
        return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à modifier ce bien.');
    }

    $validatedData = $request->validate([
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
    ], [
        'type.required' => 'Le champ type est obligatoire',
        'description.required' => 'Le champ description est obligatoire',
        'superficie.required' => 'Le champ superficie est obligatoire',
        'prix.required' => 'Le champ prix est obligatoire',
        'commune.required' => 'Le champ commune est obligatoire',
        'disponibilite.required' => 'Le champ disponibilité est obligatoire',
        'main_image.image' => 'L\'image principale doit être une image valide',
        'main_image.mimes' => 'L\'image principale doit être au format jpeg, png, jpg ou gif',
        'main_image.max' => 'L\'image principale ne doit pas dépasser 2 Mo',
        'additional_images1.image' => 'La première image supplémentaire doit être une image valide',
        'additional_images1.mimes' => 'La première image supplémentaire doit être au format jpeg, png, jpg ou gif',
        'additional_images1.max' => 'La première image supplémentaire ne doit pas dépasser 2 Mo',
        'additional_images2.image' => 'La deuxième image supplémentaire doit être une image valide',
        'additional_images2.mimes' => 'La deuxième image supplémentaire doit être au format jpeg, png, jpg ou gif',
        'additional_images2.max' => 'La deuxième image supplémentaire ne doit pas dépasser 2 Mo',
        'additional_images3.image' => 'La troisième image supplémentaire doit être une image valide',
    ]);

    // Si "Autre" est sélectionné et qu'une autre utilisation est spécifiée
    $utilisation = $validatedData['utilisation'];
    if ($utilisation === 'Autre' && !empty($validatedData['autre_utilisation'])) {
        $utilisation = $validatedData['autre_utilisation'];
    }

    // Mise à jour des propriétés
    $bien->type = $validatedData['type'];
    $bien->utilisation = $utilisation;
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
        if ($bien->image && Storage::disk('public')->exists($bien->image)) {
            Storage::disk('public')->delete($bien->image);
        }
        $mainImagePath = $request->file('main_image')->store('biens_images', 'public');
        $bien->image = $mainImagePath;
    }

    // Gestion des images supplémentaires
    for ($i = 1; $i <= 5; $i++) {
        $fieldName = 'additional_images'.$i;
        $imageField = 'image'.$i;
        
        if ($request->hasFile($fieldName)) {
            // Supprimer l'ancienne image si elle existe
            if ($bien->$imageField && Storage::disk('public')->exists($bien->$imageField)) {
                Storage::disk('public')->delete($bien->$imageField);
            }
            $bien->$imageField = $request->file($fieldName)->store('biens_images', 'public');
        }
    }

    $bien->save();

    return redirect()->route('owner.bienList')->with('success', 'Le bien a été mis à jour avec succès!');
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
    $locataire->proprietaire_id = Auth::user()->code_id;
    $locataire->status = 'Actif';
    $locataire->motif = null;
    $locataire->save();

    // Mettre à jour le statut du bien
    $bien->status = 'Loué';
    $bien->save();

    return response()->json(['success' => 'Bien attribué avec succès au locataire']);
}
}
