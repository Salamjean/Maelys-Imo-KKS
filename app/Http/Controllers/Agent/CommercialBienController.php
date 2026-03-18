<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\Agence;
use App\Models\Proprietaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CommercialBienController extends Controller
{
    public function index(Request $request)
    {
        $commercial = Auth::guard('commercial')->user();
        
        $query = Bien::with(['agence', 'proprietaire'])
                     ->where('commercial_id', $commercial->code_id);

        // Filters
        if ($request->filled('agence_id')) {
            $query->where('agence_id', $request->agence_id);
        }
        if ($request->filled('proprietaire_id')) {
            $query->where('proprietaire_id', $request->proprietaire_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $biens = $query->orderBy('created_at', 'desc')->paginate(10);
        
        $agences = Agence::where('commercial_id', $commercial->code_id)->get();
        $proprietaires = Proprietaire::where('commercial_id', $commercial->code_id)->get();

        return view('commercial.bien.index', compact('biens', 'agences', 'proprietaires'));
    }

    public function show($id)
    {
        $commercial = Auth::guard('commercial')->user();
        $bien = Bien::with(['agence', 'proprietaire'])
                    ->where('commercial_id', $commercial->code_id)
                    ->findOrFail($id);
        
        return view('commercial.bien.show', compact('bien'));
    }

    public function edit($id)
    {
        $commercial = Auth::guard('commercial')->user();
        $bien = Bien::where('commercial_id', $commercial->code_id)->findOrFail($id);
        
        $agences = Agence::all();
        $proprietaires = Proprietaire::all();
        
        return view('commercial.bien.edit', compact('bien', 'agences', 'proprietaires'));
    }

    public function update(Request $request, $id)
    {
        $commercial = Auth::guard('commercial')->user();
        $bien = Bien::where('commercial_id', $commercial->code_id)->findOrFail($id);
        
        // Similar validation and update logic as BienController...
        $validatedData = $request->validate([
            'type' => 'required|string',
            'utilisation' => 'required|string',
            'description' => 'required|string|max:255',
            'superficie' => 'required|string',
            'prix' => 'required|string',
            'commune' => 'required|string',
            'status' => 'required|string'
        ]);

        $bien->type = $request->type;
        $bien->utilisation = $request->utilisation;
        $bien->description = $request->description;
        $bien->superficie = $request->superficie;
        $bien->nombre_de_chambres = $request->nombre_de_chambres;
        $bien->nombre_de_toilettes = $request->nombre_de_toilettes;
        $bien->garage = $request->garage;
        $bien->avance = $request->avance;
        $bien->caution = $request->caution;
        $bien->frais = $request->frais;
        $bien->montant_total = $request->montant_total;
        $bien->prix = $request->prix;
        $bien->commune = $request->commune;
        $bien->video_3d = $request->video_3d;
        $bien->status = $request->status;

        // Image updates (Main)
        if ($request->hasFile('main_image')) {
            if ($bien->image) Storage::disk('public')->delete($bien->image);
            $bien->image = $request->file('main_image')->store('bien_images', 'public');
        }

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

        return redirect()->route('commercial.biens.index')->with('success', 'Bien mis à jour avec succès!');
    }

    public function destroy($id)
    {
        $commercial = Auth::guard('commercial')->user();
        $bien = Bien::where('commercial_id', $commercial->code_id)->findOrFail($id);
        
        // Delete images
        $images = ['image', 'image1', 'image2', 'image3', 'image4', 'image5'];
        foreach ($images as $imgField) {
            if ($bien->$imgField) {
                Storage::disk('public')->delete($bien->$imgField);
            }
        }

        $bien->delete();
        return redirect()->route('commercial.biens.index')->with('success', 'Bien supprimé avec succès.');
    }

    public function choice()
    {
        return view('commercial.bien.choice');
    }

    public function create(Request $request)
    {
        $type = $request->query('type');
        
        if (!in_array($type, ['agence', 'proprietaire'])) {
            return redirect()->route('commercial.biens.choice')->with('error', 'Type invalide.');
        }

        $entites = [];
        if ($type === 'agence') {
            $entites = Agence::all();
        } else {
            $entites = Proprietaire::all();
        }

        return view('commercial.bien.create', compact('type', 'entites'));
    }

    public function store(Request $request)
    {
        $type = $request->input('target_type');
        
        // Validation basique, reprise de BienController. store()
        $rules = [
            'target_id' => 'required',
            'type' => 'required|string',
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
            'main_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images1' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images4' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images5' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video_3d' => 'nullable|string'
        ];

        if ($type === 'agence') {
            $rules['target_id'] .= '|exists:agences,code_id';
        } else {
            $rules['target_id'] .= '|exists:proprietaires,code_id';
        }

        $validatedData = $request->validate($rules);

        // Upload des images (similaire à BienController)
        $mainImagePath = $request->file('main_image')->store('bien_images', 'public');
        $additionalImage1Path = $request->file('additional_images1')->store('bien_images', 'public');
        $additionalImage2Path = $request->hasFile('additional_images2') ? $request->file('additional_images2')->store('bien_images', 'public') : null;
        $additionalImage3Path = $request->hasFile('additional_images3') ? $request->file('additional_images3')->store('bien_images', 'public') : null;
        $additionalImage4Path = $request->hasFile('additional_images4') ? $request->file('additional_images4')->store('bien_images', 'public') : null;
        $additionalImage5Path = $request->hasFile('additional_images5') ? $request->file('additional_images5')->store('bien_images', 'public') : null;

        // Code generation
        $code = '';
        if ($request->type == 'Maison') {
            $code = 'BA' . mt_rand(1000, 9999);
        } elseif ($request->type == 'Appartement') {
            $code = 'AP' . mt_rand(1000, 9999);
        } else {
            $code = 'BU' . mt_rand(1000, 9999);
        }

        $bien = new Bien();
        $bien->numero_bien = $code;
        
        if ($type === 'agence') {
            $bien->agence_id = $request->target_id;
            $bien->proprietaire_id = null;
        } else {
            $bien->agence_id = null;
            $bien->proprietaire_id = $request->target_id;
        }

        $bien->type = $request->type;
        $bien->utilisation = $request->utilisation === 'Autre' ? $request->autre_utilisation : $request->utilisation;
        $bien->description = $request->description;
        $bien->superficie = $request->superficie;
        $bien->nombre_de_chambres = $request->nombre_de_chambres;
        $bien->nombre_de_toilettes = $request->nombre_de_toilettes;
        $bien->garage = $request->garage;
        $bien->avance = $request->avance;
        $bien->caution = $request->caution;
        $bien->frais = $request->frais;
        $bien->montant_total = $request->montant_total;
        $bien->prix = $request->prix;
        $bien->commune = $request->commune;
        $bien->image = $mainImagePath;
        $bien->image1 = $additionalImage1Path;
        $bien->image2 = $additionalImage2Path;
        $bien->image3 = $additionalImage3Path;
        $bien->image4 = $additionalImage4Path;
        $bien->image5 = $additionalImage5Path;
        $bien->video_3d = $request->video_3d;
        $bien->status = 'Disponible';
        $bien->commercial_id = Auth::guard('commercial')->user()->code_id;

        $bien->save();

        return redirect()->route('commercial.biens.index')->with('success', 'Bien enregistré avec succès!');
    }
}
