<?php

namespace App\Http\Controllers\Api\Authenticate;

use App\Http\Controllers\Controller;
use App\Mail\ComptablePasswordResetMail;
use App\Mail\PasswordResetOTPMail;
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
        Log::info('--- DÉBUT LOGIN ---');
        // Log::info('Données reçues:', $request->all()); 

        $request->validate([
            'code_id' => 'required|string',
            'password' => 'required|string|min:8',
            'fcm_token' => 'nullable|string',
        ]);

        try {
            // ---------------------------------------------------------
            // 2. LOGIQUE LOCATAIRE
            // ---------------------------------------------------------
            $locataire = Locataire::where('code_id', $request->code_id)->first();
            
            if ($locataire) {
                if (in_array($locataire->status, ['Inactif', 'Pas sérieux'])) {
                    return response()->json(['error' => 'Compte désactivé', 'message' => 'Votre compte est désactivé.'], 403);
                }

                if (Auth::guard('locataire')->attempt(['code_id' => $request->code_id, 'password' => $request->password])) {
                    $user = Auth::guard('locataire')->user();

                    // <--- MISE A JOUR + REFRESH --->
                    if ($request->filled('fcm_token')) {
                        $user->fcm_token = $request->fcm_token;
                        $user->save();
                        $user->refresh(); // Recharge les données depuis la BDD pour renvoyer le token
                    }
                    // <------------------------------>

                    $token = $user->createToken('LocataireAuthToken')->plainTextToken;
                    
                    return response()->json([
                        'user' => $user, 
                        'token' => $token,
                        'user_type' => 'locataire',
                        'redirect' => route('locataire.dashboard')
                    ]);
                }
            }

            // ---------------------------------------------------------
            // 3. LOGIQUE COMPTABLE
            // ---------------------------------------------------------
            $comptable = Comptable::where('code_id', $request->code_id)->first();
            
            if ($comptable) {
                if (Auth::guard('comptable')->attempt(['code_id' => $request->code_id, 'password' => $request->password])) {
                    $user = Auth::guard('comptable')->user();

                    // <--- MISE A JOUR + REFRESH --->
                    if ($request->filled('fcm_token')) {
                        $user->fcm_token = $request->fcm_token;
                        $user->save();
                        $user->refresh();
                    }
                    // <------------------------------>

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

            return response()->json([
                'error' => 'Identifiants incorrects',
                'message' => 'Le code ID ou le mot de passe est incorrect.'
            ], 401);

        } catch (Exception $e) {
            Log::error('ERREUR CONNEXION: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur de connexion'], 500);
        }
    }
    public function logout(Request $request)
    {
        // 1. Récupérer l'utilisateur connecté (Locataire ou Comptable)
        $user = auth()->user();

        if ($user) {
            // 2. Supprimer le token FCM (Important : pour ne plus recevoir de notifs sur ce téléphone)
            $user->fcm_token = null;
            $user->save();

            // 3. Supprimer le token d'authentification actuel (Sanctum)
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Déconnexion réussie'
            ]);
        }

        return response()->json(['message' => 'Utilisateur non authentifié'], 401);
    }
    /**
 * @OA\Post(
 *      path="/api/password/forgot",
 *      operationId="sendPasswordResetOTP",
 *      tags={"Authentification"},
 *      summary="Demander un OTP pour réinitialisation du mot de passe",
 *      description="Envoie un code OTP à l'email associé au code_id fourni.",
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              required={"code_id"},
 *              @OA\Property(property="code_id", type="string", example="COMPTA001")
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="OTP envoyé avec succès",
 *          @OA\JsonContent(
 *              @OA\Property(property="success", type="boolean", example=true),
 *              @OA\Property(property="message", type="string", example="Un code OTP a été envoyé à votre email")
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
public function sendResetOTP(Request $request)
{
    $request->validate([
        'code_id' => 'required|string'
    ]);

    $locataire = Locataire::where('code_id', $request->code_id)->first();
    if ($locataire) {
        return $this->handleUserOTP($locataire);
    }

    $comptable = Comptable::where('code_id', $request->code_id)->first();
    if ($comptable) {
        return $this->handleUserOTP($comptable);
    }

    return response()->json([
        'success' => false,
        'message' => 'Aucun utilisateur trouvé avec ce code ID'
    ], 404);
}

protected function handleUserOTP($user)
{
    if (empty($user->email)) {
        return response()->json([
            'success' => false,
            'message' => 'Aucun email associé à ce compte'
        ], 400);
    }

    // Générer un OTP de 6 chiffres
    $otp = rand(100000, 999999);
    $token = Str::random(60);

    // Stocker l'OTP et le token
    $user->password_reset_otp = $otp;
    $user->password_reset_token = $token;
    $user->password_reset_expires = now()->addMinutes(15); // OTP valide 15 minutes
    $user->otp_attempts = 0; // Réinitialiser les tentatives
    $user->save();

    // Envoyer l'OTP par email
    Mail::to($user->email)->send(new PasswordResetOTPMail($otp, $user->name));

    return response()->json([
        'success' => true,
        'message' => 'Un code OTP a été envoyé à votre email'
    ]);
}

/**
 * @OA\Post(
 *      path="/api/password/reset",
 *      operationId="resetPasswordWithOTP",
 *      tags={"Authentification"},
 *      summary="Réinitialiser le mot de passe avec OTP",
 *      description="Vérifie l'OTP et réinitialise le mot de passe immédiatement.",
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              required={"code_id", "otp", "password", "password_confirmation"},
 *              @OA\Property(property="code_id", type="string", example="LOC12345"),
 *              @OA\Property(property="otp", type="string", example="123456"),
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
 *          description="OTP invalide, expiré ou trop de tentatives"
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Utilisateur non trouvé"
 *      ),
 *      @OA\Response(
 *          response=422,
 *          description="Erreur de validation"
 *      )
 * )
 */
public function resetPassword(Request $request)
{
    $request->validate([
        'code_id' => 'required',
        'otp' => 'required|digits:6',
        'password' => 'required|min:8|confirmed'
    ]);

    // Trouver l'utilisateur (chercher d'abord dans locataire, puis dans comptable)
    $user = Locataire::where('code_id', $request->code_id)->first();
    
    if (!$user) {
        $user = Comptable::where('code_id', $request->code_id)->first();
    }

    if (!$user) {
        return response()->json(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
    }

    // Vérifier l'expiration
    if (now()->gt($user->password_reset_expires)) {
        return response()->json(['success' => false, 'message' => 'OTP expiré'], 400);
    }

    // Vérifier les tentatives (max 3 tentatives)
    if ($user->otp_attempts >= 3) {
        return response()->json(['success' => false, 'message' => 'Trop de tentatives. Veuillez demander un nouvel OTP'], 400);
    }

    // Vérifier l'OTP
    if ($user->password_reset_otp != $request->otp) {
        $user->otp_attempts += 1;
        $user->save();

        $remainingAttempts = 3 - $user->otp_attempts;
        
        return response()->json([
            'success' => false, 
            'message' => 'OTP incorrect',
            'remaining_attempts' => $remainingAttempts
        ], 400);
    }

    // OTP correct - réinitialiser le mot de passe
    $user->password = Hash::make($request->password);
    
    // Nettoyer tous les champs de réinitialisation
    $user->password_reset_otp = null;
    $user->password_reset_token = null;
    $user->password_reset_expires = null;
    $user->otp_attempts = 0;
    $user->save();

    return response()->json(['success' => true, 'message' => 'Mot de passe réinitialisé avec succès']);

}
public function updateFcmToken(Request $request)
{
    $request->validate([
        'fcm_token' => 'required|string',
    ]);

    // Récupère l'utilisateur connecté (Locataire ou Comptable)
    $user = auth()->user(); 

    if ($user) {
        $user->fcm_token = $request->fcm_token;
        $user->save();

        return response()->json(['message' => 'Token mis à jour avec succès']);
    }

    return response()->json(['message' => 'Utilisateur non trouvé'], 404);
}
}
