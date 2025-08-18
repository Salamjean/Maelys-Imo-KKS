<?php

namespace App\Http\Controllers\Api\Visite;

use App\Http\Controllers\Controller;
use App\Mail\VisiteConfirmation;
use App\Models\Bien;
use App\Models\Visite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * @OA\Tag(
 *     name="Visites",
 *     description="Endpoints pour la gestion des demandes de visite"
 * )
 */
class ApiVisiteController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/visit/store",
     *     summary="Créer une nouvelle demande de visite",
     *     tags={"Visites"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"bien_id", "nom", "email", "telephone", "date_visite", "heure_visite"},
     *             @OA\Property(property="bien_id", type="integer", example=1, description="ID du bien à visiter"),
     *             @OA\Property(property="nom", type="string", maxLength=255, example="John Doe", description="Nom du visiteur"),
     *             @OA\Property(property="email", type="string", format="email", maxLength=255, example="john@example.com", description="Email du visiteur"),
     *             @OA\Property(property="telephone", type="string", maxLength=20, example="07 98 27 89 81", description="Numéro de téléphone ivoirien (mobile)"),
     *             @OA\Property(property="date_visite", type="string", format="date", example="2025-12-31", description="Date souhaitée pour la visite (au format YYYY-MM-DD)"),
     *             @OA\Property(property="heure_visite", type="string", example="14:00", description="Heure souhaitée pour la visite"),
     *             @OA\Property(property="message", type="string", maxLength=500, nullable=true, example="Je souhaite visiter en matinée si possible", description="Message supplémentaire"),
     *             @OA\Property(property="statut", type="string", enum={"en attente", "confirmée", "effectuée", "annulée"}, example="en attente", description="Statut de la visite (par défaut: en attente)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Demande de visite créée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Demande de visite enregistrée avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="visite",
     *                     ref="#/components/schemas/Visite"
     *                 ),
     *                 @OA\Property(
     *                     property="bien",
     *                     ref="#/components/schemas/Bien"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur de validation"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={
     *                     "email": {"L'email doit être une adresse valide"},
     *                     "telephone": {"Le numéro doit être un mobile ivoirien valide"}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Une erreur est survenue lors de la création de la visite"),
     *             @OA\Property(property="error", type="string", example="Message d'erreur détaillé")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'bien_id' => 'required|exists:biens,id',
                'nom' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'telephone' => [
                    'required',
                    'string',
                    'max:20',
                    function ($attribute, $value, $fail) {
                        try {
                            $formatted = $this->formatIvorianNumberForTwilio($value);
                            if (!preg_match('/^\+225[1-9]\d{7}$/', $formatted)) {
                                throw new \Exception('Format invalide');
                            }
                        } catch (\Exception $e) {
                            $fail('Le numéro doit être un mobile ivoirien valide (ex: 07 98 27 89 81)');
                        }
                    }
                ],
                'date_visite' => 'required|date|after_or_equal:today',
                'heure_visite' => 'required',
                'message' => 'nullable|string|max:500',
                'statut' => 'sometimes|in:en attente,confirmée,effectuée,annulée'
            ]);

            // Définir le statut par défaut si non fourni
            if (!isset($validated['statut'])) {
                $validated['statut'] = 'en attente';
            }

            // Créer la visite
            $visite = Visite::create($validated);
            $bien = Bien::find($validated['bien_id']);

            Mail::to($validated['email'])->send(new VisiteConfirmation($visite, $bien));

            return response()->json([
                'success' => true,
                'message' => 'Demande de visite enregistrée avec succès',
                'data' => [
                    'visite' => $visite,
                    'bien' => $bien
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->validator->errors()
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création de la visite',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function formatIvorianNumberForTwilio(string $phone): string
    {
        // Nettoyage complet
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);
        
        // Si déjà au format +225...
        if (str_starts_with($cleaned, '+225') && strlen($cleaned) === 12) {
            return $cleaned;
        }
        
        // Suppression du + ou 00
        $cleaned = ltrim($cleaned, '+');
        $cleaned = preg_replace('/^00/', '', $cleaned);
        
        // Extraction des derniers 8 chiffres
        $baseNumber = substr($cleaned, -8);
        
        // Vérification du numéro mobile ivoirien
        if (!preg_match('/^[1-9]\d{7}$/', $baseNumber)) {
            throw new \Exception('Numéro mobile ivoirien invalide');
        }
        
        return '+225' . $baseNumber;
    }
}