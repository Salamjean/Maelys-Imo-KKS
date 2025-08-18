<?php

namespace App\Http\Controllers\Api\Locataire;

use App\Http\Controllers\Controller;
use App\Models\CashVerificationCode;
use App\Models\Locataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Locataires",
 *     description="Endpoints pour la gestion des locataires"
 * )
 */
class ApiLocataireController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tenant/profil/edit",
     *     summary="Récupérer le profil du locataire connecté",
     *     tags={"Locataires"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Profil récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Locataire"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Non authentifié")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Utilisateur non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur serveur"),
     *             @OA\Property(property="error", type="string", example="Message d'erreur détaillé")
     *         )
     *     )
     * )
     */
    public function edit()
    {
        try {
            $locataire = Auth::guard('sanctum')->user();
            
            if (!$locataire) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $locataire
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/tenant/profile/photo",
     *     summary="Mettre à jour la photo de profil",
     *     tags={"Locataires"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"profile_image"},
     *                 @OA\Property(
     *                     property="profile_image",
     *                     type="string",
     *                     format="binary",
     *                     description="Image de profil (jpeg,png,jpg,gif, max 2MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Photo de profil mise à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Photo de profil mise à jour avec succès"),
     *             @OA\Property(property="profile_image_url", type="string", example="http://example.com/storage/profile_images/image.jpg"),
     *             @OA\Property(
     *                 property="locataire",
     *                 type="object",
     *                 @OA\Property(property="code_id", type="string", example="LOC123"),
     *                 @OA\Property(property="name", type="string", example="John"),
     *                 @OA\Property(property="email", type="string", example="john@example.com")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Utilisateur non authentifié")
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
     *                     "profile_image": {"Le fichier doit être une image (jpeg, png, jpg, gif)"}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Échec de la mise à jour",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Échec de la mise à jour de la photo de profil"),
     *             @OA\Property(property="error", type="string", example="Message d'erreur détaillé")
     *         )
     *     )
     * )
     */
    public function updateProfilePhoto(Request $request)
    {
        try {
            $request->validate([
                'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $locataire = $request->user('sanctum');
            
            if (!$locataire) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            // Traitement de l'image
            if ($locataire->profile_image) {
                Storage::disk('public')->delete($locataire->profile_image);
            }
            
            $imagePath = $request->file('profile_image')->store('profile_images', 'public');
            $locataire->profile_image = $imagePath;
            $locataire->save();

            return response()->json([
                'success' => true,
                'message' => 'Photo de profil mise à jour avec succès',
                'profile_image_url' => asset(Storage::url($imagePath)),
                'locataire' => [
                    'code_id' => $locataire->code_id,
                    'name' => $locataire->name,
                    'email' => $locataire->email
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour photo profil: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Échec de la mise à jour de la photo de profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/tenant/profile/update-password",
     *     summary="Mettre à jour le mot de passe",
     *     tags={"Locataires"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password", "new_password", "new_password_confirmation"},
     *             @OA\Property(property="current_password", type="string", example="ancienMotDePasse"),
     *             @OA\Property(property="new_password", type="string", minLength=8, example="nouveauMotDePasse"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="nouveauMotDePasse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Mot de passe mis à jour avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Utilisateur non authentifié")
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
     *                     "current_password": {"Le champ mot de passe actuel est requis."},
     *                     "new_password_confirmation": {"Les mots de passe ne correspondent pas"}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Échec de la mise à jour",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Échec de la mise à jour du mot de passe"),
     *             @OA\Property(property="error", type="string", example="Message d'erreur détaillé")
     *         )
     *     )
     * )
     */
    public function updatePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8',
                'new_password_confirmation' => 'required|string|same:new_password'
            ], [
                'new_password_confirmation.same' => 'Les mots de passe ne correspondent pas'
            ]);

            $locataire = $request->user('sanctum');
            
            if (!$locataire) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            // Vérifier l'ancien mot de passe
            if (!Hash::check($request->current_password, $locataire->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mot de passe actuel incorrect'
                ], 422);
            }

            // Mettre à jour le mot de passe
            $locataire->password = Hash::make($request->new_password);
            $locataire->save();

            return response()->json([
                'success' => true,
                'message' => 'Mot de passe mis à jour avec succès'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour mot de passe: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Échec de la mise à jour du mot de passe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/tenant/profile/update-email",
     *     summary="Mettre à jour l'email",
     *     tags={"Locataires"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"new_email", "password"},
     *             @OA\Property(property="new_email", type="string", format="email", example="nouveau@email.com"),
     *             @OA\Property(property="password", type="string", example="motDePasseActuel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Email mis à jour avec succès"),
     *             @OA\Property(property="new_email", type="string", example="nouveau@email.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Utilisateur non authentifié")
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
     *                     "new_email": {"Cet email est déjà utilisé par un autre compte"},
     *                     "password": {"Le mot de passe est incorrect"}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Échec de la mise à jour",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Échec de la mise à jour de l'email"),
     *             @OA\Property(property="error", type="string", example="Message d'erreur détaillé")
     *         )
     *     )
     * )
     */
    public function updateEmail(Request $request)
    {
        try {
            $locataire = $request->user('sanctum');
            
            if (!$locataire) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'new_email' => 'required|email|unique:locataires,email',
                'password' => 'required|string'
            ], [
                'new_email.unique' => 'Cet email est déjà utilisé par un autre compte'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Vérifier le mot de passe
            if (!Hash::check($request->password, $locataire->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mot de passe incorrect'
                ], 422);
            }

            // Mettre à jour l'email
            $locataire->email = $request->new_email;
            $locataire->save();

            return response()->json([
                'success' => true,
                'message' => 'Email mis à jour avec succès',
                'new_email' => $locataire->email
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur mise à jour email: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Échec de la mise à jour de l\'email',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/tenant/dashboard",
     *     summary="Récupérer les données du tableau de bord",
     *     tags={"Locataires"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Données du tableau de bord récupérées",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="locataire",
     *                     ref="#/components/schemas/LocataireWithRelations"
     *                 ),
     *                 @OA\Property(
     *                     property="qr_code",
     *                     ref="#/components/schemas/CashVerificationCode"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Échec du chargement",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to load dashboard data"),
     *             @OA\Property(property="error", type="string", example="Message d'erreur détaillé")
     *         )
     *     )
     * )
     */
    public function dashboard(Request $request)
    {
        try {
            // Get authenticated locataire with relations
            $locataire = Locataire::with(['bien', 'agence'])
                            ->findOrFail(Auth::guard('sanctum')->user()->id);
            
            // Get latest valid verification code
            $qrCode = CashVerificationCode::where('locataire_id', $locataire->id)
                            ->whereNull('used_at')
                            ->where('expires_at', '>', now())
                            ->where('is_archived', false)
                            ->latest()
                            ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'locataire' => $locataire,
                    'qr_code' => $qrCode
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/tenant/contrat",
     *     summary="Récupérer le lien du contrat",
     *     tags={"Locataires"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lien du contrat récupéré",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="contrat_url", type="string", example="http://example.com/storage/contrats/contrat_123.pdf")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Contrat non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Aucun contrat trouvé pour ce locataire")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur lors de la récupération du contrat"),
     *             @OA\Property(property="error", type="string", example="Message d'erreur détaillé")
     *         )
     *     )
     * )
     */
    public function getContratLink()
    {
        try {
            // Récupérer le locataire authentifié
            $locataire = Locataire::findOrFail(Auth::guard('sanctum')->user()->id);
            
            // Vérifier si le contrat existe
            if (empty($locataire->contrat)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun contrat trouvé pour ce locataire'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'contrat_url' => $locataire->contrat
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du contrat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/tenant/{locataireId}/contrat",
     *     summary="Télécharger le contrat",
     *     tags={"Locataires"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="locataireId",
     *         in="path",
     *         required=true,
     *         description="ID du locataire",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contrat téléchargé",
     *         @OA\MediaType(
     *             mediaType="application/pdf"
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ressource non trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Locataire non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur lors du téléchargement du contrat"),
     *             @OA\Property(property="error", type="string", example="Message d'erreur détaillé")
     *         )
     *     )
     * )
     */
    public function downloadContrat($locataireId)
    {
        try {
            // Récupérer le locataire
            $locataire = Locataire::findOrFail($locataireId);
            
            // Vérifier si le contrat existe en base
            if (!$locataire->contrat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun contrat disponible pour ce locataire'
                ], 404);
            }

            // Chemin du fichier
            $path = storage_path('app/public/' . $locataire->contrat);
            
            // Vérifier si le fichier existe physiquement
            if (!file_exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le fichier contrat n\'existe pas'
                ], 404);
            }

            // Nom du fichier pour le téléchargement
            $filename = 'contrat_' . $locataire->nom . '_' . $locataire->prenom . '.' . pathinfo($path, PATHINFO_EXTENSION);

            // Téléchargement du fichier
            return response()->download($path, $filename);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Locataire non trouvé'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement du contrat',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}