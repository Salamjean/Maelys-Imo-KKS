<?php

namespace App\Http\Controllers\Agence;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use App\Models\Reversement;
use App\Models\Rib;
use App\Models\Visite;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use function Laravel\Prompts\error;

class AgenceReversementController extends Controller
{
    public function index(){
        $proprietaireId = Auth::user()->code_id;
        $agenceId = Auth::guard('agence')->user()->code_id;
        // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')
                        ->whereHas('bien', function ($query) use ($agenceId) {
                            $query->where('agence_id', $agenceId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        $reversements = Reversement::where('proprietaire_id', $proprietaireId)
            ->orderBy('created_at', 'desc')
            ->paginate(6);
        $soldeDisponible = $this->calculerSoldeDisponible($proprietaireId);
        return view('agence.reversement.index', compact('reversements', 'soldeDisponible', 'pendingVisits'));
    }
    public function uploadRecu(Request $request, $id)
    {
        // Validation du fichier
        $request->validate([
            'recu_paiement' => 'required|mimes:pdf|max:2048', // Max 2MB
        ]);

        // Récupérer l'objet reversement
        $reversement = Reversement::findOrFail($id);

        // Enregistrer le fichier
        if ($request->hasFile('recu_paiement')) {
            $file = $request->file('recu_paiement');
            $filename = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('recu_paiements', $filename, 'public');

            // Mise à jour du champ recu_paiement
            $reversement->recu_paiement = $filePath;
            $reversement->statut = 'Effectué'; // Mettre à jour le statut si nécessaire
            $reversement->save();
        }

        return redirect()->back()->with('success', 'Reçu de paiement ajouté avec succès.');
    }
    private function calculerSoldeDisponible($proprietaireId)
    {
        $totalPaiements = Paiement::where('methode_paiement', 'Mobile Money')
            ->whereHas('bien', function($query) use ($proprietaireId) {
                $query->where('agence_id', $proprietaireId);
            })
            ->where('statut', 'payé')
            ->sum('montant');
        
        $totalReversements = Reversement::where('proprietaire_id', $proprietaireId)
            ->sum('montant');
        
        return $totalPaiements - $totalReversements;
    }

    public function create()
    {
        $agenceId = Auth::guard('agence')->user()->code_id;
        // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')
                        ->whereHas('bien', function ($query) use ($agenceId) {
                            $query->where('agence_id', $agenceId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        $agenceId = Auth::user()->code_id;
        
        $ribs = Rib::where('agence_id', $agenceId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $soldeDisponible = $this->calculerSoldeDisponible($agenceId);
        
        // Récupérer les 3 derniers reversements
        $lastReversements = Reversement::with('rib')
            ->where('agence_id', $agenceId)
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get();

        return view('agence.reversement.create', compact('ribs', 'soldeDisponible', 'lastReversements', 'pendingVisits'));
    }

    private function genererReference()
    {
        $chiffres = str_shuffle('0123456789');
        $lettres = str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
        
        // Prendre 4 chiffres aléatoires
        $partieChiffres = substr($chiffres, 0, 4);
        
        // Prendre 3 lettres aléatoires
        $partieLettres = substr($lettres, 0, 3);
        
        // Combiner et mélanger
        $reference = str_shuffle($partieChiffres . $partieLettres);
        
        return $reference;
    }

    public function store(Request $request)
    {
        $agenceId = Auth::user()->code_id;
        $soldeDisponible = $this->calculerSoldeDisponible($agenceId);
        
        $request->validate([
            'banque' => 'required|exists:ribs,id',
            'rib' => 'required',
            'montant' => [
                'required',
                'numeric',
                'min:0.01',
            ],
            'date_reversement' => 'required|date',  
            'contrat' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:2048',
        ]);

        // Vérification explicite du solde
        if ($request->montant > $soldeDisponible) {
            error("Le montant demandé (".number_format($request->montant, 2)." FCFA) dépasse votre solde disponible (".number_format($soldeDisponible, 2)." FCFA)");
        }

        $recuPath = null;
        if ($request->hasFile('recu_paiement')) {
            $recuPath = $request->file('recu_paiement')->store('recu_paiement', 'public');
        }

        // Générer une référence unique
        do {
            $reference = $this->genererReference();
        } while (Reversement::where('reference', $reference)->exists());

        Reversement::create([
            'montant' => $request->montant,
            'reference' => $reference,
            'date_reversement' => $request->date_reversement,
            'recu_paiement' => $recuPath,
            'statut' => 'En attente',
            'rib_id' => $request->banque,
            'agence_id' => $agenceId,
        ]);

        return redirect()->route('reversement.create.agence')
            ->with('success', 'Reversement effectué avec succès! Référence: ' . $reference)
            ->with('solde', $this->calculerSoldeDisponible($agenceId));
    }

    public function getRib($id)
    {
        $rib = Rib::findOrFail($id);
        return response()->json(['rib' => $rib->rib]);
    }
}