<?php

namespace App\Http\Controllers\Api\Paiement;

use App\Http\Controllers\Controller;
use App\Models\Locataire;
use App\Models\Paiement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Paiements",
 *     description="Endpoints pour la gestion des paiements de loyer"
 * )
 */
class Paiementcontroller extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/tenant/{locataireId}/paiements",
     *     summary="Enregistrer un nouveau paiement",
     *     tags={"Paiements"},
     *     @OA\Parameter(
     *         name="locataireId",
     *         in="path",
     *         description="ID du locataire",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"mois_couvert", "methode_paiement"},
     *             @OA\Property(property="mois_couvert", type="string", format="date", example="2023-12", description="Mois couvert par le paiement (format YYYY-MM)"),
     *             @OA\Property(property="methode_paiement", type="string", enum={"mobile_money", "virement"}, example="mobile_money", description="Méthode de paiement"),
     *             @OA\Property(property="transaction_id", type="string", example="MM_123456789", description="ID de transaction (requis pour mobile money)"),
     *             @OA\Property(property="proof_file", type="string", format="binary", description="Fichier de preuve (requis pour virement bancaire)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Paiement enregistré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Paiement enregistré avec succès pour le mois de Décembre 2023"),
     *             @OA\Property(
     *                 property="paiement",
     *                 ref="#/components/schemas/Paiement"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Locataire non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Locataire non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Paiement déjà existant",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Paiement déjà enregistré pour Décembre 2023")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Le loyer pour Décembre 2023 a déjà été payé."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={
     *                     "mois_couvert": {"Le champ mois couvert est requis."},
     *                     "methode_paiement": {"La méthode de paiement sélectionnée est invalide."}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur lors de l'enregistrement du paiement"),
     *             @OA\Property(property="error", type="string", example="Message d'erreur détaillé")
     *         )
     *     )
     * )
     */
    public function store(Request $request, $locataireId)
    {
        $request->validate([
            'mois_couvert' => 'required|date_format:Y-m',
            'methode_paiement' => 'required|in:mobile_money,virement',
            'transaction_id' => 'required_if:methode_paiement,mobile_money',
            'proof_file' => 'required_if:methode_paiement,virement|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        try {
            $locataire = Locataire::with('bien')->findOrFail($locataireId);

            // Générer un transaction_id si absent
            $transaction_id = $request->transaction_id ?? 'VIR_' . Str::random(10);

            // Vérifier si le paiement existe déjà
            $existingPayment = Paiement::where('transaction_id', $transaction_id)->first();
            if ($existingPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paiement déjà enregistré pour ' . Carbon::parse($request->mois_couvert)->translatedFormat('F Y')
                ], 409);
            }

            // Déterminer automatiquement le mois à payer
            $dernierPaiement = Paiement::where('locataire_id', $locataire->id)
                ->where('statut', 'payé')
                ->orderBy('mois_couvert', 'desc')
                ->first();

            $moisAPayer = $dernierPaiement 
                ? Carbon::parse($dernierPaiement->mois_couvert)->addMonth()
                : now();

            // Vérifier si ce mois n'a pas déjà été payé
            $paiementExistant = Paiement::where('locataire_id', $locataire->id)
                ->where('mois_couvert', $moisAPayer->format('Y-m'))
                ->where('statut', 'payé')
                ->exists();

            if ($paiementExistant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le loyer pour '.$moisAPayer->translatedFormat('F Y').' a déjà été payé.'
                ], 422);
            }

            // Gestion du fichier de preuve
            $proofPath = null;
            if ($request->hasFile('proof_file')) {
                $proofPath = $request->file('proof_file')->store('preuves_virements', 'public');
            }

            // Déterminer la méthode et le statut
            $methode = $request->methode_paiement === 'virement' ? 'Virement Bancaire' : 'Mobile Money';
            $statut = $request->methode_paiement === 'virement' ? 'En attente' : 'payé';

            // Générer une référence unique
            do {
                $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
                $numeroId = 'PAY-' . $randomNumber;
            } while (Paiement::where('reference', $numeroId)->exists());

            // Enregistrer le paiement
            $paiement = Paiement::create([
                'montant' => $locataire->bien->montant_majore ?? $locataire->bien->prix,
                'date_paiement' => now(),
                'mois_couvert' => $moisAPayer->format('Y-m'),
                'methode_paiement' => $methode,
                'statut' => $statut,
                'reference' => $numeroId,
                'locataire_id' => $locataire->id,
                'bien_id' => $locataire->bien_id,
                'transaction_id' => $transaction_id,
                'proof_path' => $proofPath,
            ]);

            // Réinitialiser le montant majoré si nécessaire
            if ($locataire->bien->montant_majore) {
                $locataire->bien->update(['montant_majore' => null]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Paiement enregistré avec succès pour le mois de '.$moisAPayer->translatedFormat('F Y'),
                'paiement' => $paiement
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement du paiement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/tenant/{locataireId}/paiements",
     *     summary="Lister les paiements d'un locataire",
     *     tags={"Paiements"},
     *     @OA\Parameter(
     *         name="locataireId",
     *         in="path",
     *         description="ID du locataire",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des paiements récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="locataire",
     *                     ref="#/components/schemas/LocataireWithPaiements"
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="Paiements récupérés avec succès"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Locataire non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Locataire non trouvé")
     *         )
     *     )
     * )
     */
    public function index($locataireId)
    {
        try {
            // Récupérer le locataire avec ses paiements et son bien
            $locataire = Locataire::with(['paiements', 'bien'])->findOrFail($locataireId);
            
            // Formater les dates en français (si nécessaire)
            $locataire->paiements->transform(function ($paiement) {
                $paiement->created_at_formatted = Carbon::parse($paiement->created_at)->translatedFormat('d F Y');
                return $paiement;
            });

            return response()->json([
                'data' => [
                    'locataire' => $locataire,
                    'message' => 'Paiements récupérés avec succès'
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Locataire non trouvé'
            ], 404);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/tenant/paiements/{id}",
     *     summary="Afficher les détails d'un paiement",
     *     tags={"Paiements"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du paiement",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du paiement récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Détails du paiement récupérés avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/PaiementDetails"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Paiement non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Paiement non trouvé")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $paiement = Paiement::with([
                'locataire:id,name,prenom,email,contact',
                'bien:id,commune,type',
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $paiement,
                'message' => 'Détails du paiement récupérés avec succès'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Paiement non trouvé'
            ], 404);
        }
    }
}