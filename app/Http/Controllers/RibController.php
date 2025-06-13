<?php

namespace App\Http\Controllers;

use App\Models\Rib;
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
        $ribs = Rib::where('proprietaire_id', Auth::guard('owner')->user()->code_id)->get();
        return view('proprietaire.rib.create', compact('ribs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'rib' => 'required|string|unique:ribs,rib',
            'banque' => 'required|string',
        ],
        [
            'rib.required' => 'Le RIB est obligatoire.',
            'rib.unique' => 'Ce RIB existe déjà.',
            'banque.required' => 'Le nom de la banque est obligatoire.',
        ]);

        $proprietaire = Auth::guard('owner')->user();

        Rib::create([
            'rib' => $request->rib,
            'banque' => $request->banque,
            'proprietaire_id' => $proprietaire->code_id,
        ]);

        return redirect()->back()->with('success', 'RIB enregistré avec succès!');
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
        $ribs = Rib::where('proprietaire_id', Auth::guard('agence')->user()->code_id)->get();
        return view('agence.rib.create', compact('ribs'));
    }

    public function storeAgence(Request $request)
    {
        $request->validate([
            'rib' => 'required|string|unique:ribs,rib',
            'banque' => 'required|string',
        ],
        [
            'rib.required' => 'Le RIB est obligatoire.',
            'rib.unique' => 'Ce RIB existe déjà.',
            'banque.required' => 'Le nom de la banque est obligatoire.',
        ]);

        $agence = Auth::guard('agence')->user();

        Rib::create([
            'rib' => $request->rib,
            'banque' => $request->banque,
            'proprietaire_id' => $agence->code_id,
        ]);

        return redirect()->back()->with('success', 'RIB enregistré avec succès!');
    }

     public function destroyAgence($id)
    {
        $rib = Rib::findOrFail($id);
        $rib->delete(); // Supprimer le RIB

        return redirect()->route('rib.create.agence')->with('success', 'RIB supprimé avec succès.');
    }
}
