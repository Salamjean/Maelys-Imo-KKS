<?php

namespace App\Http\Controllers;

use App\Models\Bien;
use App\Models\EtatLieu;
use App\Models\Locataire;
use App\Models\Visite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EtatLieuController extends Controller
{

    //les fonctions des états des lieux par l'administrateur
    public function etat($id)
    {
        $agenceId = Auth::guard('agence')->user()->code_id;
        // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')
                        ->whereHas('bien', function ($query) use ($agenceId) {
                            $query->where('agence_id', $agenceId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        $locataire = Locataire::findOrFail($id);
        $biens = Bien::where('status', '!=', 'Loué')
                ->whereNull('agence_id')
                ->orWhere('id', $locataire->bien_id)
                ->get();
        
        return view('agence.locataire.etat', compact('locataire', 'biens','pendingVisits'));
    }

    public function store(Request $request, $id)
    {
        $locataire = Locataire::findOrFail($id);
        
        $request->validate([
            'adresse_bien' => 'nullable|string',
            'type_bien' => 'nullable|string',
            'lot' => 'nullable|string',
            'date_etat' => 'nullable|date',
            'nature_etat' => 'nullable|string',
            'nom_locataire' => 'nullable|string',
            'nom_proprietaire' => 'nullable|string',
            'presence_partie' => 'nullable|string',
            'etat_entre' => 'nullable|string',
            'etat_sorti' => 'nullable|string',
            'type_compteur' => 'nullable|string',
            'numero_compteur' => 'nullable|string',
            'releve_entre' => 'nullable|string',
            'releve_sorti' => 'nullable|string',
            'sol' => 'nullable|string',
            'murs' => 'nullable|string',
            'plafond' => 'nullable|string',
            'porte_entre' => 'nullable|string',
            'interrupteur' => 'nullable|string',
            'eclairage' => 'nullable|string',
            'remarque' => 'nullable|string',
        ]);

        $etatLieu = new EtatLieu();
        
        // Informations générales
        $etatLieu->adresse_bien = $request->adresse_bien;
        $etatLieu->type_bien = $request->type_bien;
        $etatLieu->lot = $request->lot;
        $etatLieu->date_etat = $request->date_etat;
        $etatLieu->nature_etat = $request->nature_etat;
        $etatLieu->nom_locataire = $request->nom_locataire ?? $locataire->nom_complet;
        $etatLieu->nom_proprietaire = $request->nom_proprietaire;
        $etatLieu->presence_partie = $request->presence_partie;
        $etatLieu->etat_entre = $request->etat_entre;
        $etatLieu->etat_sorti = $request->etat_sorti;
        
        // Relevés des compteurs
        $etatLieu->type_compteur = $request->type_compteur;
        $etatLieu->numero_compteur = $request->numero_compteur;
        $etatLieu->releve_entre = $request->releve_entre;
        $etatLieu->releve_sorti = $request->releve_sorti;
        
        // État des lieux par pièce
        $etatLieu->sol = $request->sol;
        $etatLieu->murs = $request->murs;
        $etatLieu->plafond = $request->plafond;
        $etatLieu->porte_entre = $request->porte_entre;
        $etatLieu->interrupteur = $request->interrupteur;
        $etatLieu->eclairage = $request->eclairage;
        $etatLieu->remarque = $request->remarque;
        
        // Clés étrangères
        $etatLieu->locataire_id = $locataire->code_id;
        $etatLieu->proprietaire_id = $locataire->proprietaire_id;
        $etatLieu->agence_id = $locataire->agence_id;
        
        $etatLieu->save();
        
        return redirect()->route('locataire.index')->with('success', 'État des lieux enregistré avec succès.');
    }




    //les fonctions des états des lieux par l'administrateur
    public function etatAdmin($id)
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
        $locataire = Locataire::findOrFail($id);
        $biens = Bien::where('status', '!=', 'Loué')
                ->whereNull('agence_id')
                ->orWhere('id', $locataire->bien_id)
                ->get();
        
        return view('admin.locataire.etat', compact('locataire', 'biens','pendingVisits'));
    }

    public function storeAdmin(Request $request, $id)
    {
        $locataire = Locataire::findOrFail($id);
        
        $request->validate([
            'adresse_bien' => 'nullable|string',
            'type_bien' => 'nullable|string',
            'lot' => 'nullable|string',
            'date_etat' => 'nullable|date',
            'nature_etat' => 'nullable|string',
            'nom_locataire' => 'nullable|string',
            'nom_proprietaire' => 'nullable|string',
            'presence_partie' => 'nullable|string',
            'etat_entre' => 'nullable|string',
            'etat_sorti' => 'nullable|string',
            'type_compteur' => 'nullable|string',
            'numero_compteur' => 'nullable|string',
            'releve_entre' => 'nullable|string',
            'releve_sorti' => 'nullable|string',
            'sol' => 'nullable|string',
            'murs' => 'nullable|string',
            'plafond' => 'nullable|string',
            'porte_entre' => 'nullable|string',
            'interrupteur' => 'nullable|string',
            'eclairage' => 'nullable|string',
            'remarque' => 'nullable|string',
        ]);

        $etatLieu = new EtatLieu();
        
        // Informations générales
        $etatLieu->adresse_bien = $request->adresse_bien;
        $etatLieu->type_bien = $request->type_bien;
        $etatLieu->lot = $request->lot;
        $etatLieu->date_etat = $request->date_etat;
        $etatLieu->nature_etat = $request->nature_etat;
        $etatLieu->nom_locataire = $request->nom_locataire ?? $locataire->nom_complet;
        $etatLieu->nom_proprietaire = $request->nom_proprietaire;
        $etatLieu->presence_partie = $request->presence_partie;
        $etatLieu->etat_entre = $request->etat_entre;
        $etatLieu->etat_sorti = $request->etat_sorti;
        
        // Relevés des compteurs
        $etatLieu->type_compteur = $request->type_compteur;
        $etatLieu->numero_compteur = $request->numero_compteur;
        $etatLieu->releve_entre = $request->releve_entre;
        $etatLieu->releve_sorti = $request->releve_sorti;
        
        // État des lieux par pièce
        $etatLieu->sol = $request->sol;
        $etatLieu->murs = $request->murs;
        $etatLieu->plafond = $request->plafond;
        $etatLieu->porte_entre = $request->porte_entre;
        $etatLieu->interrupteur = $request->interrupteur;
        $etatLieu->eclairage = $request->eclairage;
        $etatLieu->remarque = $request->remarque;
        
        // Clés étrangères
        $etatLieu->locataire_id = $locataire->code_id;
        $etatLieu->proprietaire_id = $locataire->proprietaire_id;
        $etatLieu->agence_id = $locataire->agence_id;
        
        $etatLieu->save();
        
        return redirect()->route('locataire.admin.index')->with('success', 'État des lieux enregistré avec succès.');
    }




     //les fonctions des états des lieux par l'administrateur
    public function etatOwner($id)
    {
        $locataire = Locataire::findOrFail($id);
        $biens = Bien::where('status', '!=', 'Loué')
                ->whereNull('agence_id')
                ->orWhere('id', $locataire->bien_id)
                ->get();
        
        return view('proprietaire.locataire.etat', compact('locataire', 'biens'));
    }

    public function storeOwner(Request $request, $id)
    {
        $locataire = Locataire::findOrFail($id);
        
        $request->validate([
            'adresse_bien' => 'nullable|string',
            'type_bien' => 'nullable|string',
            'lot' => 'nullable|string',
            'date_etat' => 'nullable|date',
            'nature_etat' => 'nullable|string',
            'nom_locataire' => 'nullable|string',
            'nom_proprietaire' => 'nullable|string',
            'presence_partie' => 'nullable|string',
            'etat_entre' => 'nullable|string',
            'etat_sorti' => 'nullable|string',
            'type_compteur' => 'nullable|string',
            'numero_compteur' => 'nullable|string',
            'releve_entre' => 'nullable|string',
            'releve_sorti' => 'nullable|string',
            'sol' => 'nullable|string',
            'murs' => 'nullable|string',
            'plafond' => 'nullable|string',
            'porte_entre' => 'nullable|string',
            'interrupteur' => 'nullable|string',
            'eclairage' => 'nullable|string',
            'remarque' => 'nullable|string',
        ]);

        $etatLieu = new EtatLieu();
        
        // Informations générales
        $etatLieu->adresse_bien = $request->adresse_bien;
        $etatLieu->type_bien = $request->type_bien;
        $etatLieu->lot = $request->lot;
        $etatLieu->date_etat = $request->date_etat;
        $etatLieu->nature_etat = $request->nature_etat;
        $etatLieu->nom_locataire = $request->nom_locataire ?? $locataire->nom_complet;
        $etatLieu->nom_proprietaire = $request->nom_proprietaire;
        $etatLieu->presence_partie = $request->presence_partie;
        $etatLieu->etat_entre = $request->etat_entre;
        $etatLieu->etat_sorti = $request->etat_sorti;
        
        // Relevés des compteurs
        $etatLieu->type_compteur = $request->type_compteur;
        $etatLieu->numero_compteur = $request->numero_compteur;
        $etatLieu->releve_entre = $request->releve_entre;
        $etatLieu->releve_sorti = $request->releve_sorti;
        
        // État des lieux par pièce
        $etatLieu->sol = $request->sol;
        $etatLieu->murs = $request->murs;
        $etatLieu->plafond = $request->plafond;
        $etatLieu->porte_entre = $request->porte_entre;
        $etatLieu->interrupteur = $request->interrupteur;
        $etatLieu->eclairage = $request->eclairage;
        $etatLieu->remarque = $request->remarque;
        
        // Clés étrangères
        $etatLieu->locataire_id = $locataire->code_id;
        $etatLieu->proprietaire_id = $locataire->proprietaire_id;
        $etatLieu->agence_id = $locataire->agence_id;
        
        $etatLieu->save();
        
        return redirect()->route('locataire.index.owner')->with('success', 'État des lieux enregistré avec succès.');
    }

}
