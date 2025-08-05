<?php

namespace App\Http\Controllers;

use App\Models\Rib;
use App\Models\Visite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RibController extends Controller
{
    public function index()
    {
        // Logic to retrieve and display RIBs
    }
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
        $ribs = Rib::where('proprietaire_id', Auth::guard('owner')->user()->code_id)->get();
        return view('proprietaire.rib.create', compact('ribs', 'pendingVisits'));
    }

    public function store(Request $request)
    {
        $proprietaire = Auth::guard('owner')->user();
        $maxRibs = 2; // Nombre maximum de RIBs autorisés

        // Vérification du quota
        $existingRibsCount = Rib::where('proprietaire_id', $proprietaire->code_id)->count();
        
        if ($existingRibsCount >= $maxRibs) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Vous avez atteint le nombre maximum de IBAN autorisés ('.$maxRibs.')');
        }

        // Validation
        $request->validate([
            'rib' => 'required|string|unique:ribs,rib',
            'banque' => 'required|string',
            'path_rib_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'rib.required' => 'Le IBAN est obligatoire.',
            'rib.unique' => 'Ce IBAN est déjà enregistré pour votre compte.',
            'banque.required' => 'Le nom de la banque est obligatoire.',
            'path_rib_file.required' => 'Le fichier IBAN est obligatoire.',
            'path_rib_file.mimes' => 'Le fichier doit être au format JPG, JPEG, PNG ou PDF.',
            'path_rib_file.max' => 'Le fichier ne doit pas dépasser 2Mo.',
        ]);

        // Traitement du fichier RIB
        $ribPath = $request->file('path_rib_file')->store('ribs/proprietaires', 'public');

        // Création du RIB
        Rib::create([
            'rib' => $request->rib,
            'banque' => $request->banque,
            'path_rib_file' => $ribPath,
            'proprietaire_id' => $proprietaire->code_id,
        ]);

        return redirect()->back()->with('success', 'IBAN enregistré avec succès!');
    }

     public function destroy($id)
    {
        $rib = Rib::findOrFail($id);
        $rib->delete(); // Supprimer le RIB

        return redirect()->route('rib.create')->with('success', 'RIB supprimé avec succès.');
    }

    //les routes pour les RIBs par l'agence
    public function createAgence()
    {
        $agenceId = Auth::guard('agence')->user()->code_id;
        // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')
                        ->whereHas('bien', function ($query) use ($agenceId) {
                            $query->where('agence_id', $agenceId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        $ribs = Rib::where('agence_id', Auth::guard('agence')->user()->code_id)->get();
        return view('agence.rib.create', compact('ribs', 'pendingVisits'));
    }

    public function storeAgence(Request $request)
    {
        // Vérifier d'abord le nombre de RIB existants
        $agence = Auth::guard('agence')->user();
        $maxRibs = 2; // Définir le nombre maximum autorisé

        if (Rib::where('agence_id', $agence->code_id)->count() >= $maxRibs) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Vous avez atteint le nombre maximum de IBAN autorisés ('.$maxRibs.')');
        }

        // Validation des données
        $request->validate([
            'rib' => 'required|string|unique:ribs,rib',
            'banque' => 'required|string',
            'path_rib_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'rib.required' => 'L\'IBAN est obligatoire.',
            'rib.unique' => 'Cet IBAN est déjà associé à une banque.',
            'banque.required' => 'Le nom de la banque est obligatoire.',
            'path_rib_file.required' => 'Le fichier IBAN est obligatoire'
        ]);

        // Traitement du fichier
        $ribPath = $request->file('path_rib_file')->store('fichier_rib', 'public');

        // Création du RIB
        Rib::create([
            'rib' => $request->rib,
            'banque' => $request->banque,
            'path_rib_file' => $ribPath,
            'agence_id' => $agence->code_id,
        ]);

        return redirect()->back()->with('success', 'IBAN enregistré avec succès !');
    }

     public function destroyAgence($id)
    {
        $rib = Rib::findOrFail($id);
        $rib->delete(); // Supprimer le RIB

        return redirect()->route('rib.create.agence')->with('success', 'RIB supprimé avec succès.');
    }
}
