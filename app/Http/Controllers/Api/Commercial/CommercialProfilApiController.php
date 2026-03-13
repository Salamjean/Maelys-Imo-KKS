<?php

namespace App\Http\Controllers\Api\Commercial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

/**
 * @OA\Tag(
 *     name="Commercial - Profil",
 *     description="Gestion du profil du commercial"
 * )
 */
class CommercialProfilApiController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/commercial/profil/edit",
     *      operationId="getCommercialProfil",
     *      tags={"Commercial - Profil"},
     *      summary="Récupérer les informations du profil",
     *      security={{"sanctum":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="commercial", type="object")
     *          )
     *      )
     * )
     */
    public function edit()
    {
        $commercial = auth()->user();
        if (!$commercial || !($commercial instanceof \App\Models\Commercial)) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        return response()->json([
            'success' => true,
            'commercial' => $commercial
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/commercial/profil/update",
     *      operationId="updateCommercialProfil",
     *      tags={"Commercial - Profil"},
     *      summary="Mettre à jour les informations personnelles",
     *      security={{"sanctum":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="prenom", type="string"),
     *              @OA\Property(property="commune", type="string"),
     *              @OA\Property(property="contact", type="string"),
     *              @OA\Property(property="date_naissance", type="string", format="date")
     *          )
     *      ),
     *      @OA\Response(response=200, description="Profil mis à jour")
     * )
     */
    public function update(Request $request)
    {
        $commercial = auth()->user();
        if (!$commercial || !($commercial instanceof \App\Models\Commercial)) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'commune' => 'nullable|string|max:255',
            'contact' => 'required|string|max:20',
            'date_naissance' => 'nullable|date',
        ]);

        try {
            $commercial->update($request->only(['name', 'prenom', 'commune', 'contact', 'date_naissance']));

            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès',
                'commercial' => $commercial
            ]);
        } catch (Exception $e) {
            Log::error('API Error updating commercial profil: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la mise à jour.'], 500);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/commercial/profil/photo",
     *      operationId="updateCommercialPhoto",
     *      tags={"Commercial - Profil"},
     *      summary="Mettre à jour la photo de profil",
     *      security={{"sanctum":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="profile_image", type="string", format="binary")
     *              )
     *          )
     *      ),
     *      @OA\Response(response=200, description="Photo mise à jour")
     * )
     */
    public function updatePhoto(Request $request)
    {
        $commercial = auth()->user();
        if (!$commercial || !($commercial instanceof \App\Models\Commercial)) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            if ($commercial->profile_image) {
                Storage::disk('public')->delete($commercial->profile_image);
            }

            $path = $request->file('profile_image')->store('commercials/profiles', 'public');
            $commercial->profile_image = $path;
            $commercial->save();

            return response()->json([
                'success' => true,
                'message' => 'Photo de profil mise à jour',
                'profile_image_url' => asset('storage/' . $path)
            ]);
        } catch (Exception $e) {
            Log::error('API Error updating commercial photo: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la mise à jour de la photo.'], 500);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/commercial/profil/update-password",
     *      operationId="updateCommercialPassword",
     *      tags={"Commercial - Profil"},
     *      summary="Mettre à jour le mot de passe",
     *      security={{"sanctum":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="current_password", type="string"),
     *              @OA\Property(property="new_password", type="string", minLength=8),
     *              @OA\Property(property="new_password_confirmation", type="string")
     *          )
     *      ),
     *      @OA\Response(response=200, description="Mot de passe mis à jour")
     * )
     */
    public function updatePassword(Request $request)
    {
        $commercial = auth()->user();
        if (!$commercial || !($commercial instanceof \App\Models\Commercial)) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $commercial->password)) {
            return response()->json(['error' => 'Mot de passe actuel incorrect'], 422);
        }

        try {
            $commercial->password = Hash::make($request->new_password);
            $commercial->save();

            return response()->json([
                'success' => true,
                'message' => 'Mot de passe mis à jour avec succès'
            ]);
        } catch (Exception $e) {
            Log::error('API Error updating commercial password: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la mise à jour du mot de passe.'], 500);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/commercial/profil/update-email",
     *      operationId="updateCommercialEmail",
     *      tags={"Commercial - Profil"},
     *      summary="Mettre à jour l'email",
     *      security={{"sanctum":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="new_email", type="string", format="email"),
     *              @OA\Property(property="password", type="string")
     *          )
     *      ),
     *      @OA\Response(response=200, description="Email mis à jour")
     * )
     */
    public function updateEmail(Request $request)
    {
        $commercial = auth()->user();
        if (!$commercial || !($commercial instanceof \App\Models\Commercial)) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $request->validate([
            'new_email' => 'required|email|unique:commercials,email',
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, $commercial->password)) {
            return response()->json(['error' => 'Mot de passe incorrect'], 422);
        }

        try {
            $commercial->email = $request->new_email;
            $commercial->save();

            return response()->json([
                'success' => true,
                'message' => 'Email mis à jour avec succès',
                'new_email' => $commercial->email
            ]);
        } catch (Exception $e) {
            Log::error('API Error updating commercial email: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la mise à jour de l\'email.'], 500);
        }
    }
}
