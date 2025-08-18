<?php

namespace App\Http\Controllers\Api\Authenticate;

use App\Http\Controllers\Controller;
use App\Mail\ComptablePasswordResetMail;
use App\Models\Comptable;
use App\Models\Locataire;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Authentification",
 *     description="Points de terminaison pour la connexion et la gestion des mots de passe"
 * )
 */
class UserAuthentucateController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/login",
     *      operationId="loginUser",
     *      tags={"Authentification"},
     *      summary="Connecter un utilisateur (locataire ou comptable)",
     *      description="Authentifie un utilisateur avec son code_id et mot de passe. Le système vérifie s'il s'agit d'un locataire ou d'un comptable et renvoie un token Sanctum en cas de succès.",
     *      @OA\RequestBody(
     *          required=true,
     *          description="Identifiants de l'utilisateur",
     *          @OA\JsonContent(
     *              required={"code_id", "password"},
     *              @OA\Property(property="code_id", type="string", example="LOC12345"),
     *              @OA\Property(property="password", type="string", format="password", example="password123")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Connexion réussie",
     *          @OA\JsonContent(
     *              @OA\Property(property="user", type="object", description="Données de l'utilisateur"),
     *              @OA\Property(property="token", type="string", description="Token d'authentification Sanctum"),
     *              @OA\Property(property="user_type", type="string", example="locataire"),
     *              @OA\Property(property="redirect", type="string", example="http://localhost/locataire/dashboard")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Identifiants incorrects",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="Identifiants incorrects")
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Compte désactivé",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="Compte désactivé")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Erreur de validation",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Erreur serveur"
     *      )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'code_id' => 'required|string',
            'password' => 'required|string|min:8',
        ], [
            'code_id.required' => 'Le code ID est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit avoir au moins 8 caractères.',
        ]);

        try {
            // Essayer d'abord comme locataire
            $locataire = Locataire::where('code_id', $request->code_id)->first();
            
            if ($locataire) {
                // Vérifier le statut du locataire
                if (in_array($locataire->status, ['Inactif', 'Pas sérieux'])) {
                    return response()->json([
                        'error' => 'Compte désactivé',
                        'message' => 'Votre compte est désactivé. Veuillez contacter votre propriétaire/agence pour plus d\'informations.',
                        'status' => $locataire->status
                    ], 403);
                }

                if (Auth::guard('locataire')->attempt(['code_id' => $request->code_id, 'password' => $request->password])) {
                    $user = Auth::guard('locataire')->user();
                     $token = $user->createToken('LocataireAuthToken')->plainTextToken;
                    
                    return response()->json([
                        'user' => $user,
                        'token' => $token,
                        'user_type' => 'locataire',
                        'redirect' => route('locataire.dashboard')
                    ]);
                }
            }

            // Essayer comme comptable si pas trouvé comme locataire
            $comptable = Comptable::where('code_id', $request->code_id)->first();
            
            if ($comptable) {
                if (Auth::guard('comptable')->attempt(['code_id' => $request->code_id, 'password' => $request->password])) {
                    $user = Auth::guard('comptable')->user();
                    $token = $user->createToken('ComptableAuthToken')->plainTextToken;
                    
                    $redirect = $user->user_type === 'Agent de recouvrement' 
                        ? route('accounting.agent.dashboard') 
                        : route('accounting.dashboard');
                    
                    return response()->json([
                        'user' => $user,
                        'token' => $token,
                        'user_type' => 'comptable',
                        'role' => $user->user_type,
                        'redirect' => $redirect
                    ]);
                }
            }

            // Si aucun des deux
            return response()->json([
                'error' => 'Identifiants incorrects',
                'message' => 'Le code ID ou le mot de passe est incorrect.'
            ], 401);

        } catch (Exception $e) {
            Log::error('Erreur survenue : '.$e->getMessage()."\n".$e->getTraceAsString());
            return response()->json([
                'error' => 'Erreur de connexion',
                'message' => 'Une erreur est survenue lors de la connexion.'
            ], 500);
        }
    }


    /**
     * @OA\Post(
     *      path="/api/password/forgot",
     *      operationId="sendPasswordResetLink",
     *      tags={"Authentification"},
     *      summary="Demander la réinitialisation du mot de passe",
     *      description="Envoie un lien de réinitialisation de mot de passe à l'email associé au code_id fourni.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"code_id"},
     *              @OA\Property(property="code_id", type="string", example="COMPTA001")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Lien de réinitialisation envoyé",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Un lien de réinitialisation a été envoyé à votre email")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Aucun email associé"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Utilisateur non trouvé"
     *      )
     * )
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'code_id' => 'required|string'
        ]);

        $locataire = Locataire::where('code_id', $request->code_id)->first();
        if ($locataire) {
            return $this->handleUserReset($locataire, 'locataire');
        }

        $comptable = Comptable::where('code_id', $request->code_id)->first();
        if ($comptable) {
            return $this->handleUserReset($comptable, 'comptable');
        }

        return response()->json([
            'success' => false,
            'message' => 'Aucun utilisateur trouvé avec ce code ID'
        ], 404);
    }

    protected function handleUserReset($user, $userType)
    {
        if (empty($user->email)) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun email associé à ce compte'
            ], 400);
        }

        $token = Str::random(60);
        $user->password_reset_token = $token;
        $user->password_reset_expires = now()->addHours(1);
        $user->save();

        $resetLink = url("/api/password/reset?token=$token&code_id={$user->code_id}&type=$userType");
        Mail::to($user->email)->send(new ComptablePasswordResetMail($resetLink));

        return response()->json([
            'success' => true,
            'message' => 'Un lien de réinitialisation a été envoyé à votre email'
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/password/reset",
     *      operationId="resetUserPassword",
     *      tags={"Authentification"},
     *      summary="Réinitialiser le mot de passe avec un token",
     *      description="Définit un nouveau mot de passe pour un utilisateur en utilisant le token de réinitialisation.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"token", "code_id", "type", "password", "password_confirmation"},
     *              @OA\Property(property="token", type="string", example="longrandomtokenstring..."),
     *              @OA\Property(property="code_id", type="string", example="LOC12345"),
     *              @OA\Property(property="type", type="string", enum={"locataire", "comptable"}, example="locataire"),
     *              @OA\Property(property="password", type="string", format="password", example="NouveauMotDePasse123"),
     *              @OA\Property(property="password_confirmation", type="string", format="password", example="NouveauMotDePasse123")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Mot de passe réinitialisé avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Mot de passe réinitialisé avec succès")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Token invalide ou expiré"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Utilisateur non trouvé"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Erreur de validation (ex: mots de passe non identiques)"
     *      )
     * )
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'code_id' => 'required',
            'type' => 'required|in:locataire,comptable',
            'password' => 'required|min:8|confirmed'
        ]);

        $user = $request->type === 'locataire' 
            ? Locataire::where('code_id', $request->code_id)->first()
            : Comptable::where('code_id', $request->code_id)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
        }

        if ($user->password_reset_token !== $request->token || 
            now()->gt($user->password_reset_expires)) {
            return response()->json(['success' => false, 'message' => 'Lien de réinitialisation invalide ou expiré'], 400);
        }

        $user->password = Hash::make($request->password);
        $user->password_reset_token = null;
        $user->password_reset_expires = null;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Mot de passe réinitialisé avec succès']);
    }
}