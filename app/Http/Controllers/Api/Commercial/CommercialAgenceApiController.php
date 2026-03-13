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
                ->notify(new SendEmailToAgenceAfterRegistrationNotification($code, $agence->email));

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
}
