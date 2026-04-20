<?php

namespace App\Http\Controllers;

use App\Models\Bien;
use App\Models\EtatLieu;
use App\Models\EtatLieuSorti;
use App\Models\HistoriqueLocation;
use App\Models\Locataire;
use App\Models\Visite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDF;

class DemenagementController extends Controller
{
    /**
     * Affiche la page de déménagement : état des lieux de sortie + confirmation
     */
    public function show($locataire_id)
    {
        $pendingVisits = $this->getPendingVisits();
        $layout = $this->getLayout();
        $routePrefix = $this->routePrefix();

        $locataire = Locataire::with(['bien', 'agence', 'proprietaire'])->findOrFail($locataire_id);

        if (!$locataire->bien) {
            return back()->with('error', 'Ce locataire n\'a pas de bien associé.');
        }

        // Chercher l'historique en cours
        $historiqueEnCours = HistoriqueLocation::where('locataire_id', $locataire_id)
            ->whereNull('date_sortie')
            ->with(['etatLieuEntree', 'bien', 'agence', 'proprietaire'])
            ->latest()
            ->first();

        // Etat des lieux d'entrée (le plus récent pour ce locataire+bien)
        $etatEntree = EtatLieu::where('locataire_id', $locataire_id)
            ->where('bien_id', $locataire->bien_id)
            ->latest()
            ->first();

        if ($etatEntree) {
            $etatEntree->parties_communes = json_decode($etatEntree->parties_communes, true);
            $etatEntree->chambres         = json_decode($etatEntree->chambres, true);
        }

        // Vérifier si un état de sortie existe déjà
        $etatSortieExistant = EtatLieuSorti::where('locataire_id', $locataire_id)
            ->where('bien_id', $locataire->bien_id)
            ->latest()
            ->first();

        return view('demenagement.show', compact(
            'locataire',
            'historiqueEnCours',
            'etatEntree',
            'etatSortieExistant',
            'pendingVisits',
            'layout',
            'routePrefix'
        ));
    }

    /**
     * Enregistre l'état des lieux de sortie ET ferme la location
     */
    public function confirmer(Request $request, $locataire_id)
    {
        $validated = $request->validate([
            'motif_sortie'               => 'required|string|max:255',
            'date_sortie'                => 'required|date',
            'presence_partie'            => 'required|in:oui,non',
            'parties_communes.sol'       => 'nullable|string',
            'parties_communes.observation_sol'       => 'nullable|string',
            'parties_communes.murs'      => 'nullable|string',
            'parties_communes.observation_murs'      => 'nullable|string',
            'parties_communes.plafond'   => 'nullable|string',
            'parties_communes.observation_plafond'   => 'nullable|string',
            'parties_communes.porte_entre'           => 'nullable|string',
            'parties_communes.observation_porte_entre' => 'nullable|string',
            'parties_communes.interrupteur'          => 'nullable|string',
            'parties_communes.observation_interrupteur' => 'nullable|string',
            'parties_communes.robinet'   => 'nullable|string',
            'parties_communes.observation_robinet'   => 'nullable|string',
            'parties_communes.lavabo'    => 'nullable|string',
            'parties_communes.observation_lavabo'    => 'nullable|string',
            'parties_communes.douche'    => 'nullable|string',
            'parties_communes.observation_douche'    => 'nullable|string',
            'chambres'                   => 'required|array|min:1',
            'chambres.*.nom'             => 'required|string',
            'chambres.*.sol'             => 'nullable|string',
            'chambres.*.observation_sol' => 'nullable|string',
            'chambres.*.murs'            => 'nullable|string',
            'chambres.*.observation_murs' => 'nullable|string',
            'chambres.*.plafond'         => 'nullable|string',
            'chambres.*.observation_plafond' => 'nullable|string',
            'nombre_cle'                 => 'required|integer|min:0',
        ]);

        $locataire = Locataire::with('bien')->findOrFail($locataire_id);

        if (!$locataire->bien) {
            return back()->with('error', 'Ce locataire n\'a pas de bien associé.');
        }

        DB::transaction(function () use ($validated, $locataire) {
            // 1. Créer l'état des lieux de sortie
            $etatSortie = EtatLieuSorti::create([
                'locataire_id'     => $locataire->id,
                'bien_id'          => $locataire->bien_id,
                'type_bien'        => $locataire->bien->type ?? null,
                'commune_bien'     => $locataire->bien->commune ?? null,
                'presence_partie'  => $validated['presence_partie'],
                'status_sorti'     => 'Confirmé',
                'parties_communes' => json_encode($validated['parties_communes']),
                'chambres'         => json_encode($validated['chambres']),
                'nombre_cle'       => $validated['nombre_cle'],
            ]);

            // 2. Fermer l'historique en cours
            $historique = HistoriqueLocation::where('locataire_id', $locataire->id)
                ->whereNull('date_sortie')
                ->latest()
                ->first();

            if ($historique) {
                $historique->update([
                    'date_sortie'         => $validated['date_sortie'],
                    'motif_sortie'        => $validated['motif_sortie'],
                    'etat_lieu_sortie_id' => $etatSortie->id,
                ]);
            }

            // 3. Libérer le bien
            $bien = $locataire->bien;
            $bien->status = 'Disponible';
            $bien->save();

            // 4. Désaffecter le locataire
            $locataire->status          = 'Inactif';
            $locataire->motif           = $validated['motif_sortie'];
            $locataire->bien_id         = null;
            $locataire->agence_id       = null;
            $locataire->proprietaire_id = null;
            $locataire->save();
        });

        return redirect()->route($this->routeHistorique(), $locataire_id)
            ->with('success', 'Déménagement confirmé. État des lieux de sortie enregistré.');
    }

    /**
     * Retourne le nom de route historique selon le guard connecté
     */
    private function routeHistorique(): string
    {
        if (Auth::guard('agence')->check()) {
            return 'agence.demenagement.historique';
        }
        if (Auth::guard('owner')->check()) {
            return 'owner.demenagement.historique';
        }
        return 'demenagement.historique';
    }

    /**
     * Calcule le nombre de visites en attente selon le guard connecté
     */
    private function getPendingVisits(): int
    {
        if (Auth::guard('agence')->check()) {
            $agenceId = Auth::guard('agence')->user()->code_id;
            return Visite::where('statut', 'en attente')
                ->whereHas('bien', fn($q) => $q->where('agence_id', $agenceId))
                ->count();
        }
        if (Auth::guard('owner')->check()) {
            $ownerId = Auth::guard('owner')->user()->code_id;
            return Visite::where('statut', 'en attente')
                ->whereHas('bien', fn($q) => $q->where('proprietaire_id', $ownerId))
                ->count();
        }
        // admin
        return Visite::where('statut', 'en attente')->count();
    }

    private function getLayout(): string
    {
        if (Auth::guard('agence')->check()) {
            return 'agence.layouts.template';
        }
        if (Auth::guard('owner')->check()) {
            return 'proprietaire.layouts.template';
        }
        return 'admin.layouts.template';
    }

    private function routePrefix(): string
    {
        if (Auth::guard('agence')->check()) {
            return 'agence.';
        }
        if (Auth::guard('owner')->check()) {
            return 'owner.';
        }
        return '';
    }

    /**
     * Historique complet des locations d'un locataire
     */
    public function historique($locataire_id)
    {
        $pendingVisits = $this->getPendingVisits();
        $layout = $this->getLayout();
        $routePrefix = $this->routePrefix();

        $locataire = Locataire::findOrFail($locataire_id);

        $historiques = HistoriqueLocation::with(['bien', 'agence', 'proprietaire', 'etatLieuEntree', 'etatLieuSortie'])
            ->where('locataire_id', $locataire_id)
            ->orderBy('date_entree', 'desc')
            ->get();

        return view('demenagement.historique', compact('locataire', 'historiques', 'pendingVisits', 'layout', 'routePrefix'));
    }

    /**
     * Télécharger le PDF de l'état des lieux de sortie d'une location
     */
    public function downloadSortie($historique_id)
    {
        $historique = HistoriqueLocation::with(['locataire', 'bien', 'etatLieuSortie'])->findOrFail($historique_id);

        if (!$historique->etatLieuSortie) {
            return back()->with('error', 'Aucun état des lieux de sortie pour cette location.');
        }

        $etatLieuSorti = $historique->etatLieuSortie;
        $etatLieuSorti->parties_communes = json_decode($etatLieuSorti->parties_communes, true);
        $etatLieuSorti->chambres         = json_decode($etatLieuSorti->chambres, true);

        $pdf = PDF::loadView('agent.etat_lieu.pdf_sorti', compact('etatLieuSorti'));

        return $pdf->download('etat-sortie-' . $historique->locataire->name . '-' . $historique->date_sortie->format('d-m-Y') . '.pdf');
    }
}
