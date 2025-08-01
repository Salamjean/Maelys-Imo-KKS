<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use App\Models\Visite;
use PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AbonnementController extends Controller
{
    public function abonnement()
{
    // Rediriger vers la connexion si non authentifié
    if (!auth('owner')->check()) {
        return redirect()->route('owner.login')
            ->with('info', 'Veuillez vous connecter pour souscrire à un abonnement');
    }

    // Récupérer l'utilisateur connecté
    $proprietaire = Auth::guard('owner')->user();
    
    // Vérifier l'abonnement actif
    $abonnementActif = Abonnement::where('proprietaire_id', $proprietaire->code_id)
                                ->where('date_fin', '>=', now())
                                ->where('statut', 'actif')
                                ->exists();
    
   

    // Options d'abonnement
    $abonnements = [
        [
            'duree' => 1,
            'prix' => 100,
            'label' => '1 mois'
        ],
        [
            'duree' => 3,
            'prix' => 300,
            'label' => '3 mois'
        ],
        [
            'duree' => 6,
            'prix' => 600,
            'label' => '6 mois'
        ],
        [
            'duree' => 12,
            'prix' => 1200,
            'label' => '1 an'
        ]
    ];

    // Retourner la vue avec les données nécessaires
    return view('home.abonnement.proprietaire', [
        'abonnements' => $abonnements,
        'proprietaire' => $proprietaire
    ]);
}

   public function activateAccount(Request $request)
{
    Log::info('Début activation compte - Données reçues:', $request->all());

    // Validation des données
    $validated = $request->validate([
        'transaction_id' => 'required|string',
        'amount' => 'required|numeric|min:100',
        'duration' => 'required|integer|in:1,3,6,12', // Ajout du champ duration
        'type' => 'required|string|in:standard,premium'
    ]);

    DB::beginTransaction();
    Log::info('Transaction DB démarrée');

    try {
        // Récupération de l'utilisateur authentifié
        $proprietaire = Auth::guard('owner')->user();
        if (!$proprietaire) {
            Log::error('Utilisateur non authentifié');
            throw new \Exception('Utilisateur non authentifié');
        }

        Log::info('Utilisateur récupéré:', ['id' => $proprietaire->id, 'code_id' => $proprietaire->code_id]);

        // Calcul de la date de fin en fonction de la durée
        $today = now();
        $dateFin = $today->copy();
        
        switch ($validated['duration']) {
            case 1:
                $dateFin->addMonth();
                break;
            case 3:
                $dateFin->addMonths(3);
                break;
            case 6:
                $dateFin->addMonths(6);
                break;
            case 12:
                $dateFin->addYear();
                break;
        }

        // Recherche d'un abonnement existant ou création
        $abonnement = Abonnement::firstOrNew([
            'proprietaire_id' => $proprietaire->code_id
        ]);

        // Mise à jour des données de l'abonnement
        $abonnement->fill([
            'date_abonnement' => $today,
            'date_debut' => $today,
            'date_fin' => $dateFin,
            'mois_abonne' => $today->format('m-Y'),
            'montant' => $validated['amount'],
            'duree_mois' => $validated['duration'], // Ajout de la durée en mois
            'type' => $validated['type'],
            'statut' => 'actif',
            'mode_paiement' => 'Mobile Money',
            'reference_paiement' => $validated['transaction_id'],
            'notes' => 'Abonnement de ' . $validated['duration'] . ' mois - Mis à jour le ' . $today->format('d/m/Y H:i'),
        ]);

        // Sauvegarde de l'abonnement
        if (!$abonnement->save()) {
            Log::error('Échec de la sauvegarde de l\'abonnement');
            throw new \Exception('Impossible d\'enregistrer l\'abonnement');
        }

        Log::info('Abonnement sauvegardé avec succès', ['id' => $abonnement->id]);

        DB::commit();

        return redirect()->route('owner.dashboard')
            ->with('success', 'Abonnement activé avec succès! Valable jusqu\'au ' . $dateFin->format('d/m/Y'));

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Erreur dans l\'activation de l\'abonnement', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return back()->with('error', 'Erreur technique: ' . $e->getMessage());
    }
}

    public function handleCinetPayNotification(Request $request)
{
    Log::info('Notification CinetPay reçue:', $request->all());

    // Exemple de données attendues : transaction_id, status, amount
    $transactionId = $request->input('transaction_id');
    $status = $request->input('status');
    $amount = $request->input('amount');

    try {
        if ($status === 'ACCEPTED') {
            $abonnement = Abonnement::where('reference_paiement', $transactionId)->first();

            if ($abonnement) {
                $abonnement->statut = 'actif';
                $abonnement->montant = $amount;
                $abonnement->date_fin = now()->addMonth();
                $abonnement->save();

                Log::info('Abonnement activé avec succès après notification', ['transaction_id' => $transactionId]);
            } else {
                Log::error('Abonnement introuvable pour la transaction', ['transaction_id' => $transactionId]);
            }
        } else {
            Log::error('Paiement non accepté', ['transaction_id' => $transactionId, 'status' => $status]);
        }
    } catch (\Exception $e) {
        Log::error('Erreur dans le traitement de la notification CinetPay', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
}

public function renew(Request $request)
{
    $validated = $request->validate([
        'id' => 'required|exists:abonnements,id',
        'type' => 'required|in:standard,premium', // Note: vérifiez l'orthographe de 'premium'
        'duree' => 'required|integer|in:1,3,6,12',
        'montant' => 'required|numeric|min:0',
        'reduction' => 'required|numeric|min:0|max:100'
    ]);

    $abonnement = Abonnement::findOrFail($validated['id']);

    // Vérifier si le type d'abonnement change
    if ($abonnement->type === $validated['type']) {
        // Même type - on ajoute les mois à la date de fin existante
        $dateFin = Carbon::parse($abonnement->date_fin)->addMonths($validated['duree']);
    } else {
        // Type différent - on part de la date actuelle
        $dateFin = now()->addMonths($validated['duree']);
    }

    $abonnement->update([
        'type' => $validated['type'],
        'date_debut' => now(), // Toujours mettre à jour la date de début pour le nouveau cycle
        'date_fin' => $dateFin,
        'montant' => $validated['montant'],
        'montant_actuel' => $validated['montant'],
        'mois_abonne' => now()->format('m-Y'),
        'mode_paiement' => 'Mobile Money',
        'statut' => 'actif',
        'reduction_appliquee' => $validated['reduction']
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Abonnement renouvelé avec succès',
        'new_end_date' => $dateFin->format('d/m/Y'),
        'amount_paid' => $validated['montant']
    ]);
}
public function renewAgence(Request $request)
{
    $validated = $request->validate([
        'id' => 'required|exists:abonnements,id',
        'type' => 'required|in:standard,premium', // Note: vérifiez l'orthographe de 'premium'
        'duree' => 'required|integer|in:1,3,6,12',
        'montant' => 'required|numeric|min:0',
        'reduction' => 'required|numeric|min:0|max:100'
    ]);

    $abonnement = Abonnement::findOrFail($validated['id']);

    // Vérifier si le type d'abonnement change
    if ($abonnement->type === $validated['type']) {
        // Même type - on ajoute les mois à la date de fin existante
        $dateFin = Carbon::parse($abonnement->date_fin)->addMonths($validated['duree']);
    } else {
        // Type différent - on part de la date actuelle
        $dateFin = now()->addMonths($validated['duree']);
    }

    $abonnement->update([
        'type' => $validated['type'],
        'date_debut' => now(), // Toujours mettre à jour la date de début pour le nouveau cycle
        'date_fin' => $dateFin,
        'montant' => $validated['montant'],
        'montant_actuel' => $validated['montant'],
        'mois_abonne' => now()->format('m-Y'),
        'mode_paiement' => 'Mobile Money',
        'statut' => 'actif',
        'reduction_appliquee' => $validated['reduction']
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Abonnement renouvelé avec succès',
        'new_end_date' => $dateFin->format('d/m/Y'),
        'amount_paid' => $validated['montant']
    ]);
}
    // Méthode optionnelle pour vérifier le paiement avec l'API CinetPay
    protected function verifyCinetPayPayment($transactionId)
    {
        // Implémentez la vérification avec l'API CinetPay
        // Retourne true si le paiement est vérifié, false sinon
        return true; // Pour la démo, on suppose que le paiement est valide
    }
   public function abonnementAgence()
{
    // Vérification de l'authentification
    if (!auth('agence')->check()) {
        return redirect()->route('agence.login')
            ->with('info', 'Veuillez vous connecter pour souscrire à un abonnement');
    }

    // Récupération de l'agence
    $agence = Auth::guard('agence')->user();
    Log::info('Accès page abonnement agence', ['agence_id' => $agence->code_id]);

    // Vérification abonnement actif existant
    $abonnementActif = Abonnement::where('agence_id', $agence->code_id)
                                ->where('date_fin', '>=', now())
                                ->where('statut', 'actif')
                                ->first();

    // Configuration des plans d'abonnement
    $abonnements = [
        'standard' => [
            [
                'duree' => 1,
                'prix' => 10000,
                'label' => '1 Mois Standard',
                'features' => [
                    'Gestion jusqu\'à 20 biens',
                    '5 comptes agents inclus',
                    'Support technique standard',
                    'Tableau de bord de base'
                ]
            ],
            [
                'duree' => 3,
                'prix' => 24000, // 8.000/mois
                'label' => '3 Mois Standard',
                'features' => [
                    'Gestion jusqu\'à 50 biens',
                    '10 comptes agents inclus',
                    'Support prioritaire',
                    'Statistiques avancées'
                ]
            ]
        ],
        'premium' => [
            [
                'duree' => 1,
                'prix' => 15000,
                'label' => '1 Mois Premium',
                'features' => [
                    'Biens illimités',
                    '20 comptes agents inclus',
                    'Listage en avant',
                    'Support premium 24/7',
                    'Analytics complets'
                ]
            ],
            [
                'duree' => 3,
                'prix' => 36000, // 12.000/mois
                'label' => '3 Mois Premium',
                'features' => [
                    'Biens et agents illimités',
                    'Positionnement prioritaire',
                    'Badge Premium visible',
                    'Support VIP dédié',
                    'Rapports personnalisés'
                ]
            ]
        ]
    ];

    Log::debug('Configuration des abonnements chargée', [
        'standard_count' => count($abonnements['standard']),
        'premium_count' => count($abonnements['premium'])
    ]);

    return view('home.abonnement.agence', [
        'abonnements' => $abonnements,
        'agence' => $agence,
        'hasActiveSubscription' => false
    ]);
}

public function activateAccountAgence(Request $request)
{
    Log::info('Début activation compte agence - Données reçues:', $request->all());

    // Validation des données (identique à celle des propriétaires)
    $validated = $request->validate([
        'transaction_id' => 'required|string',
        'amount' => 'required|numeric|min:100',
        'duration' => 'required|integer|in:1,3,6,12',
        'type' => 'required|string|in:standard,premium'
    ]);

    DB::beginTransaction();
    Log::info('Transaction DB démarrée pour agence');

    try {
        // Récupération de l'agence authentifiée
        $agence = Auth::guard('agence')->user();
        if (!$agence) {
            Log::error('Agence non authentifiée');
            throw new \Exception('Agence non authentifiée');
        }

        Log::info('Agence récupérée:', ['id' => $agence->id, 'code_id' => $agence->code_id]);

        // Calcul de la date de fin (même logique que propriétaire)
        $today = now();
        $dateFin = $today->copy();
        
        switch ($validated['duration']) {
            case 1: $dateFin->addMonth(); break;
            case 3: $dateFin->addMonths(3); break;
            case 6: $dateFin->addMonths(6); break;
            case 12: $dateFin->addYear(); break;
        }

        // Recherche d'un abonnement existant ou création
        $abonnement = Abonnement::firstOrNew([
            'agence_id' => $agence->code_id // Seule différence ici (agence_id au lieu de proprietaire_id)
        ]);

        // Mise à jour des données (identique à propriétaire)
        $abonnement->fill([
            'date_abonnement' => $today,
            'date_debut' => $today,
            'date_fin' => $dateFin,
            'mois_abonne' => $today->format('m-Y'),
            'montant' => $validated['amount'],
            'duree_mois' => $validated['duration'],
            'type' => $validated['type'],
            'statut' => 'actif',
            'mode_paiement' => 'Mobile Money',
            'reference_paiement' => $validated['transaction_id'],
            'notes' => 'Abonnement agence ' . $validated['type'] . ' ' . $validated['duration'] . ' mois - ' . $today->format('d/m/Y H:i'),
            // Les champs proprietaire_id resteront null pour les agences
        ]);

        if (!$abonnement->save()) {
            Log::error('Échec de la sauvegarde de l\'abonnement agence');
            throw new \Exception('Impossible d\'enregistrer l\'abonnement');
        }

        Log::info('Abonnement agence sauvegardé avec succès', [
            'id' => $abonnement->id,
            'type' => $validated['type']
        ]);

        DB::commit();

        return redirect()->route('agence.dashboard')
            ->with('success', 'Abonnement ' . $validated['type'] . ' activé! Valable jusqu\'au ' . $dateFin->format('d/m/Y'));

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Erreur activation abonnement agence', [
            'error' => $e->getMessage(),
            'request' => $request->all()
        ]);

        return back()
            ->withInput()
            ->with('error', 'Erreur technique: ' . $e->getMessage());
    }
}

    public function handleCinetPayNotificationAgence(Request $request)
{
    Log::info('Notification CinetPay reçue:', $request->all());

    // Exemple de données attendues : transaction_id, status, amount
    $transactionId = $request->input('transaction_id');
    $status = $request->input('status');
    $amount = $request->input('amount');

    try {
        if ($status === 'ACCEPTED') {
            $abonnement = Abonnement::where('reference_paiement', $transactionId)->first();

            if ($abonnement) {
                $abonnement->statut = 'actif';
                $abonnement->montant = $amount;
                $abonnement->date_fin = now()->addMonth();
                $abonnement->save();

                Log::info('Abonnement activé avec succès après notification', ['transaction_id' => $transactionId]);
            } else {
                Log::error('Abonnement introuvable pour la transaction', ['transaction_id' => $transactionId]);
            }
        } else {
            Log::error('Paiement non accepté', ['transaction_id' => $transactionId, 'status' => $status]);
        }
    } catch (\Exception $e) {
        Log::error('Erreur dans le traitement de la notification CinetPay', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
}
    // Méthode optionnelle pour vérifier le paiement avec l'API CinetPay
    protected function verifyCinetPayPaymentAgence($transactionId)
    {
        // Implémentez la vérification avec l'API CinetPay
        // Retourne true si le paiement est vérifié, false sinon
        return true; // Pour la démo, on suppose que le paiement est valide
    }


    //Les fonction pour les abonnements des agences sont similaires à celles des propriétaires
    public function abonneActif()
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
        // Date actuelle
        $today = now()->format('Y-m-d');

        // Récupérer les abonnements actifs
        $abonnementsActifs = Abonnement::with('agence') // Charge la relation agence
            ->where('statut', 'actif')
            ->where('date_fin', '>=', $today) // Date fin non dépassée
            ->orderBy('date_fin', 'asc') // Tri par date de fin croissante
            ->paginate(10);

        return view('admin.abonnement.actif', compact('abonnementsActifs', 'pendingVisits'));
    }
    public function abonneInactif()
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
        $today = now()->format('Y-m-d');

        // Récupérer les abonnements inactifs (soit statut inactif, soit date dépassée)
        $abonnementsInactifs = Abonnement::with(['proprietaire', 'agence'])
            ->where(function($query) use ($today) {
                $query->where('statut', 'inactif')
                    ->orWhere('date_fin', '<', $today);
            })
            ->orderBy('date_fin', 'desc') // Tri par date de fin décroissante
            ->paginate(10);

        return view('admin.abonnement.inactif', compact('abonnementsInactifs', 'pendingVisits'));
    }

    public function activate(Request $request)
{
    $request->validate([
        'id' => 'required|exists:abonnements,id',
        'months' => 'nullable|integer|min:1|max:12' // Optionnel (uniquement pour le prolongement)
    ]);

    try {
        $abonnement = Abonnement::findOrFail($request->id);
        
        // Cas 1 : Activation simple (sans modification de durée)
        if (!$request->has('months')) {
            $abonnement->update([
                'statut' => 'actif',
                'notes' => 'Activé sans modification de durée le '.now()->format('d/m/Y H:i')
            ]);
        } 
        // Cas 2 : Prolongation (avec durée)
        else {
            $abonnement->update([
                'statut' => 'actif',
                'date_debut' => now(),
                'date_fin' => now()->addMonths($request->months),
                'mois_abonne' => now()->format('m-Y').' à '.now()->addMonths($request->months)->format('m-Y'),
                'notes' => 'Prolongé manuellement le '.now()->format('d/m/Y H:i').' pour '.$request->months.' mois'
            ]);
        }

        return response()->json(['success' => true]);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Erreur lors de l\'activation: '.$e->getMessage()
        ], 500);
    }
}

    public function deactivate(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:abonnements,id'
        ]);

        try {
            $abonnement = Abonnement::findOrFail($request->id);
            
            $abonnement->update([
                'statut' => 'inactif',
                'date_fin' => now(), // Met fin immédiatement à l'abonnement
                'notes' => 'Désactivé manuellement par admin le '.now()->format('d/m/Y')
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la désactivation: '.$e->getMessage()
            ], 500);
        }
    }

    public function extend(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:abonnements,id',
            'months' => 'required|integer|min:1',
            'user_type' => 'required|in:Propriétaire,Agence',
        ]);

        $abonnement = Abonnement::find($validated['id']);
        
        // Calcul du montant à ajouter en fonction du type d'utilisateur
        $prixMensuel = $validated['user_type'] === 'Propriétaire' ? 5000 : 10000;
        $montantAjoute = $validated['months'] * $prixMensuel;

        // Mise à jour de l'abonnement
        $abonnement->date_fin = Carbon::parse($abonnement->date_fin)
            ->addMonths($validated['months']);
        
        $abonnement->montant_actuel = $montantAjoute;
        $abonnement->mode_paiement = 'Espèces';
        $abonnement->montant += $montantAjoute;
        $abonnement->save();

        return response()->json([
            'success' => true,
            'nouveau_montant' => $abonnement->montant,
            'montant_actuel' => $abonnement->montant_actuel,
            'nouvelle_date_fin' => $abonnement->date_fin->format('d/m/Y')
        ]);
    }

    public function reduce(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:abonnements,id',
            'months' => 'required|integer|min:1',
            'user_type' => 'required|in:Propriétaire,Agence'
        ]);

        $abonnement = Abonnement::find($validated['id']);
        
        // Calcul du montant à retirer en fonction du type d'utilisateur
        $prixMensuel = $validated['user_type'] === 'Propriétaire' ? 5000 : 10000;
        $montantRetire = $validated['months'] * $prixMensuel;

        // Vérifier qu'on ne retire pas plus que le montant existant
        if ($montantRetire >= $abonnement->montant) {
            return response()->json([
                'success' => false,
                'message' => 'Le montant à retirer est supérieur au montant actuel'
            ], 400);
        }

        // Mise à jour de l'abonnement
        $abonnement->date_fin = Carbon::parse($abonnement->date_fin)
            ->subMonths($validated['months']);
        
        $abonnement->montant_actuel -= $montantRetire;
        $abonnement->montant -= $montantRetire;
        $abonnement->save();

        return response()->json([
            'success' => true,
            'nouveau_montant' => $abonnement->montant,
            'nouvelle_date_fin' => $abonnement->date_fin->format('d/m/Y')
        ]);
    }

    public function generatePDF($id)
    {
        Carbon::setLocale('fr');
        $abonnement = Abonnement::with(['proprietaire', 'agence'])->findOrFail($id);
        
        // Déterminer le nom de l'abonné
        $abonneName = 'N/A';
        if ($abonnement->proprietaire) {
            $abonneName = $abonnement->proprietaire->name.' '.$abonnement->proprietaire->prenom ?? 'Propriétaire';
        } elseif ($abonnement->agence) {
            $abonneName = $abonnement->agence->name ?? 'Agence';
        }
        
        // Calcul des jours restants
        $joursRestants = now()->diffInDays($abonnement->date_fin, false);
        $joursRestants = $joursRestants > 0 ? $joursRestants : 0;

        $pdf = PDF::loadView('admin.abonnement.pdf', [
            'abonnement' => $abonnement,
            'abonneName' => $abonneName,
            'joursRestants' => $joursRestants
        ]);

        return $pdf->download('abonnement-'.$abonnement->id.'.pdf');
    }

    public function abonneShow(){
        $proprietaireId = Auth::guard('owner')->user()->code_id;
          $ownerId = Auth::guard('owner')->user()->code_id;
     // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')->where('statut', '!=', 'effectuée')
                        ->where('statut', '!=', 'annulée')
                        ->whereHas('bien', function ($query) use ($ownerId) {
                             $query->where('proprietaire_id', $ownerId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        $abonnements = Abonnement::where('proprietaire_id', $proprietaireId)->paginate(1);
        return view('proprietaire.abonnement.show-proprietaire',compact('abonnements', 'pendingVisits'));
    }
    public function abonneShowAgence()
    {
        $agenceId = Auth::guard('agence')->user()->code_id;
        // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')
                        ->whereHas('bien', function ($query) use ($agenceId) {
                            $query->where('agence_id', $agenceId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
            $agenceId = Auth::guard('agence')->user()->code_id;
        $abonnements = Abonnement::where('agence_id', $agenceId)->paginate(1);
        return view('agence.abonnement.show-agence',compact('abonnements', 'pendingVisits'));
    }
}
