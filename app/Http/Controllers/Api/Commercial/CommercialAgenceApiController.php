<?php

namespace App\Http\Controllers\Api\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Abonnement;
use App\Models\Agence;
use App\Models\ResetCodePasswordAgence;
use App\Notifications\SendEmailToAgenceAfterRegistrationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Exception;

/**
 * @OA\Tag(
 *     name="Commercial - Agences",
 *     description="Gestion des agences par le commercial"
 * )
 */
class CommercialAgenceApiController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/commercial/agences",
     *      operationId="getCommercialAgences",
     *      tags={"Commercial - Agences"},
     *      summary="Liste des agences du commercial",
     *      description="Renvoie la liste des agences enregistrées par le commercial connecté.",
     *      security={{"sanctum":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="agences", type="array", @OA\Items(type="object"))
     *          )
     *      ),
     *      @OA\Response(response=401, description="Non authentifié")
     * )
     */
    public function index()
    {
        $commercial = auth()->user();

        if (!$commercial || !($commercial instanceof \App\Models\Commercial)) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $agences = Agence::where('commercial_id', $commercial->code_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'agences' => $agences
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/commercial/agences",
     *      operationId="storeAgenceByCommercial",
     *      tags={"Commercial - Agences"},
     *      summary="Ajouter une nouvelle agence",
     *      description="Permet à un commercial d'enregistrer une nouvelle agence. Un abonnement de 3 mois est créé automatiquement et un email est envoyé à l'agence.",
     *      security={{"sanctum":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  required={"name", "email", "contact", "commune", "adresse", "rib", "rccm", "rccm_file", "dfe", "dfe_file"},
     *                  @OA\Property(property="name", type="string"),
     *                  @OA\Property(property="email", type="string", format="email"),
     *                  @OA\Property(property="contact", type="string"),
     *                  @OA\Property(property="commune", type="string"),
     *                  @OA\Property(property="adresse", type="string"),
     *                  @OA\Property(property="rib", type="string", format="binary"),
     *                  @OA\Property(property="rccm", type="string"),
     *                  @OA\Property(property="rccm_file", type="string", format="binary"),
     *                  @OA\Property(property="dfe", type="string"),
     *                  @OA\Property(property="dfe_file", type="string", format="binary"),
     *                  @OA\Property(property="profile_image", type="string", format="binary")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Agence créée avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="agence", type="object")
     *          )
     *      ),
     *      @OA\Response(response=422, description="Erreur de validation"),
     *      @OA\Response(response=403, description="Accès non autorisé")
     * )
     */
    public function store(Request $request)
    {
        $commercial = auth()->user();

        if (!$commercial || !($commercial instanceof \App\Models\Commercial)) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:agences,email',
            'contact' => 'required|string|min:10',
            'commune' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'rib' => 'required|file|mimes:pdf|max:2048',
            'rccm' => 'required|string|max:255',
            'rccm_file' => 'required|file|mimes:pdf|max:2048',
            'dfe' => 'required|string|max:255',
            'dfe_file' => 'required|file|mimes:pdf|max:2048',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Génération du code PRO unique
            do {
                $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
                $codeId = 'AG' . $randomNumber;
            } while (Agence::where('code_id', $codeId)->exists());

            // Dossiers de stockage
            $profileImagePath = $request->hasFile('profile_image') ? $request->file('profile_image')->store('profile_images', 'public') : null;
            $ribPath = $request->file('rib')->store('ribs', 'public');
            $rccmPath = $request->file('rccm_file')->store('rccm_files', 'public');
            $dfe_filePath = $request->file('dfe_file')->store('dfe_files', 'public');

            // Création de l'agence
            $agence = new Agence();
            $agence->code_id = $codeId;
            $agence->name = $request->name;
            $agence->email = $request->email;
            $agence->contact = $request->contact;
            $agence->commune = $request->commune;
            $agence->adresse = $request->adresse;
            $agence->rccm_file = $rccmPath;
            $agence->dfe_file = $dfe_filePath;
            $agence->rccm = $request->rccm;
            $agence->dfe = $request->dfe;
            $agence->rib = $ribPath;
            $agence->password = Hash::make('password'); // Mot de passe par défaut à changer par l'agence
            $agence->profile_image = $profileImagePath;
            $agence->commercial_id = $commercial->code_id;
            $agence->save();

            // Création automatique de l'abonnement (3 mois offerts)
            $today = now();
            Abonnement::create([
                'agence_id' => $agence->code_id,
                'date_abonnement' => $today,
                'date_debut' => $today->format('Y-m-d'),
                'date_fin' => $today->copy()->addMonths(3)->format('Y-m-d'),
                'mois_abonne' => $today->format('m-Y'),
                'montant' => 0,
                'montant_actuel' => 0,
                'statut' => 'actif',
                'type' => 'standard',
                'mode_paiement' => 'offert',
                'reference_paiement' => 'CREA-' . $agence->code_id,
                'notes' => 'Abonnement créé automatiquement via API Commercial',
            ]);

            // Envoi de l'e-mail de vérification / définition du mot de passe
            ResetCodePasswordAgence::where('email', $agence->email)->delete();
            $code = rand(1000, 4000);
            ResetCodePasswordAgence::create([
                'code' => $code,
                'email' => $agence->email,
            ]);

            Notification::route('mail', $agence->email)
                ->notify(new SendEmailToAgenceAfterRegistrationNotification($code, $agence->email, $agence->code_id));

            return response()->json([
                'success' => true,
                'message' => 'Agence enregistrée avec succès. Un email a été envoyé à l\'agence pour définir son mot de passe.',
                'agence' => $agence
            ], 201);
        } catch (Exception $e) {
            Log::error('API Error creating agence par commercial: ' . $e->getMessage());
            return response()->json(['error' => 'Une erreur est survenue lors de la création de l\'agence.'], 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/commercial/agences/{id}",
     *      operationId="getCommercialAgenceById",
     *      tags={"Commercial - Agences"},
     *      summary="Détails d'une agence",
     *      description="Récupère les détails d'une agence spécifique appartenant au commercial.",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Code ID de l'agence",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="agence", type="object")
     *          )
     *      ),
     *      @OA\Response(response=404, description="Agence non trouvée"),
     *      @OA\Response(response=403, description="Accès non autorisé")
     * )
     */
    public function show($id)
    {
        $commercial = auth()->user();

        if (!$commercial || !($commercial instanceof \App\Models\Commercial)) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $agence = Agence::where('code_id', $id)
            ->where('commercial_id', $commercial->code_id)
            ->first();

        if (!$agence) {
            return response()->json(['error' => 'Agence non trouvée'], 404);
        }

        return response()->json([
            'success' => true,
            'agence' => $agence
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/commercial/agences/{id}/update",
     *      operationId="updateAgenceByCommercial",
     *      tags={"Commercial - Agences"},
     *      summary="Modifier une agence",
     *      description="Permet à un commercial de modifier les informations d'une agence.",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Code ID de l'agence",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="name", type="string"),
     *                  @OA\Property(property="email", type="string", format="email"),
     *                  @OA\Property(property="contact", type="string"),
     *                  @OA\Property(property="commune", type="string"),
     *                  @OA\Property(property="adresse", type="string"),
     *                  @OA\Property(property="rib", type="string", format="binary"),
     *                  @OA\Property(property="rccm", type="string"),
     *                  @OA\Property(property="rccm_file", type="string", format="binary"),
     *                  @OA\Property(property="dfe", type="string"),
     *                  @OA\Property(property="dfe_file", type="string", format="binary"),
     *                  @OA\Property(property="profile_image", type="string", format="binary")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Agence modifiée avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="agence", type="object")
     *          )
     *      ),
     *      @OA\Response(response=422, description="Erreur de validation"),
     *      @OA\Response(response=404, description="Agence non trouvée"),
     *      @OA\Response(response=403, description="Accès non autorisé")
     * )
     */
    public function update(Request $request, $id)
    {
        $commercial = auth()->user();

        if (!$commercial || !($commercial instanceof \App\Models\Commercial)) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $agence = Agence::where('code_id', $id)
            ->where('commercial_id', $commercial->code_id)
            ->first();

        if (!$agence) {
            return response()->json(['error' => 'Agence non trouvée'], 404);
        }

        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'email',
                \Illuminate\Validation\Rule::unique('agences', 'email')->ignore($agence->id),
            ],
            'contact' => 'nullable|string|min:10',
            'commune' => 'nullable|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'rib' => 'nullable|file|mimes:pdf|max:2048',
            'rccm' => 'nullable|string|max:255',
            'rccm_file' => 'nullable|file|mimes:pdf|max:2048',
            'dfe' => 'nullable|string|max:255',
            'dfe_file' => 'nullable|file|mimes:pdf|max:2048',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Mise à jour des informations textes
            if ($request->has('name')) $agence->name = $request->name;
            if ($request->has('email')) $agence->email = $request->email;
            if ($request->has('contact')) $agence->contact = $request->contact;
            if ($request->has('commune')) $agence->commune = $request->commune;
            if ($request->has('adresse')) $agence->adresse = $request->adresse;
            if ($request->has('rccm')) $agence->rccm = $request->rccm;
            if ($request->has('dfe')) $agence->dfe = $request->dfe;

            // Gestion des fichiers
            if ($request->hasFile('profile_image')) {
                if ($agence->profile_image) Storage::disk('public')->delete($agence->profile_image);
                $agence->profile_image = $request->file('profile_image')->store('profile_images', 'public');
            }

            if ($request->hasFile('rib')) {
                if ($agence->rib) Storage::disk('public')->delete($agence->rib);
                $agence->rib = $request->file('rib')->store('ribs', 'public');
            }

            if ($request->hasFile('rccm_file')) {
                if ($agence->rccm_file) Storage::disk('public')->delete($agence->rccm_file);
                $agence->rccm_file = $request->file('rccm_file')->store('rccm_files', 'public');
            }

            if ($request->hasFile('dfe_file')) {
                if ($agence->dfe_file) Storage::disk('public')->delete($agence->dfe_file);
                $agence->dfe_file = $request->file('dfe_file')->store('dfe_files', 'public');
            }

            $agence->save();

            return response()->json([
                'success' => true,
                'message' => 'Informations de l\'agence mises à jour avec succès.',
                'agence' => $agence
            ]);
        } catch (Exception $e) {
            Log::error('API Error updating agence by commercial: ' . $e->getMessage());
            return response()->json(['error' => 'Une erreur est survenue lors de la mise à jour des informations.'], 500);
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/commercial/agences/{id}",
     *      operationId="deleteAgenceByCommercial",
     *      tags={"Commercial - Agences"},
     *      summary="Supprimer une agence",
     *      description="Permet à un commercial de supprimer une agence qu'il a ajoutée, ainsi que ses abonnements et fichiers associés.",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Code ID de l'agence",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Agence supprimée avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string")
     *          )
     *      ),
     *      @OA\Response(response=403, description="Accès non autorisé ou agence avec biens actifs"),
     *      @OA\Response(response=404, description="Agence non trouvée")
     * )
     */
    public function destroy($id)
    {
        $commercial = auth()->user();

        if (!$commercial || !($commercial instanceof \App\Models\Commercial)) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $agence = Agence::where('code_id', $id)
            ->where('commercial_id', $commercial->code_id)
            ->first();

        if (!$agence) {
            return response()->json(['error' => 'Agence non trouvée'], 404);
        }

        // Vérification de sécurité : ne pas supprimer si l'agence a des biens gérés via elle
        if ($agence->biens()->exists()) {
            return response()->json(['error' => 'Impossible de supprimer une agence possédant des biens enregistrés.'], 403);
        }

        try {
            DB::beginTransaction();

            // Suppression des abonnements
            Abonnement::where('agence_id', $agence->code_id)->delete();

            // Suppression des fichiers physiques
            $filesToDelete = [
                $agence->profile_image,
                $agence->rib,
                $agence->rccm_file,
                $agence->dfe_file
            ];

            foreach ($filesToDelete as $filePath) {
                if ($filePath && Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }

            $agence->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'L\'agence et ses données associées ont été supprimées avec succès.'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('API Error deleting agence by commercial: ' . $e->getMessage());
            return response()->json(['error' => 'Une erreur est survenue lors de la suppression de l\'agence.'], 500);
        }
    }
}
