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
        Log::info('Tentative de connexion API', [
            'code_id' => $request->code_id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'origin' => $request->header('origin'),
            'timestamp' => now()
        ]);

        $request->validate([
            'code_id' => 'required|string',
            'password' => 'required|string|min:8',
        ], [
            'code_id.required' => 'Le code ID est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit avoir au moins 8 caractères.',
        ]);

        try {
            Log::debug('Recherche utilisateur', ['code_id' => $request->code_id]);

            // Essayer d'abord comme locataire
            $locataire = Locataire::where('code_id', $request->code_id)->first();
            
            if ($locataire) {
                Log::debug('Locataire trouvé', ['id' => $locataire->id, 'status' => $locataire->status]);

                // Vérifier le statut du locataire
                if (in_array($locataire->status, ['Inactif', 'Pas sérieux'])) {
                    Log::warning('Compte locataire désactivé', [
                        'code_id' => $request->code_id,
                        'status' => $locataire->status
                    ]);

                    return response()->json([
                        'error' => 'Compte désactivé',
                        'message' => 'Votre compte est désactivé. Veuillez contacter votre propriétaire/agence pour plus d\'informations.',
                        'status' => $locataire->status
                    ], 403);
                }

                if (Auth::guard('locataire')->attempt(['code_id' => $request->code_id, 'password' => $request->password])) {
                    $user = Auth::guard('locataire')->user();
                    $token = $user->createToken('LocataireAuthToken')->plainTextToken;
                    
                    Log::info('Connexion locataire réussie', [
                        'user_id' => $user->id,
                        'code_id' => $user->code_id
                    ]);

                    return response()->json([
                        'user' => $user,
                        'token' => $token,
                        'user_type' => 'locataire',
                        'redirect' => route('locataire.dashboard')
                    ]);
                } else {
                    Log::warning('Mot de passe locataire incorrect', ['code_id' => $request->code_id]);
                }
            }

            // Essayer comme comptable si pas trouvé comme locataire
            $comptable = Comptable::where('code_id', $request->code_id)->first();
            
            if ($comptable) {
                Log::debug('Comptable trouvé', ['id' => $comptable->id, 'user_type' => $comptable->user_type]);

                if (Auth::guard('comptable')->attempt(['code_id' => $request->code_id, 'password' => $request->password])) {
                    $user = Auth::guard('comptable')->user();
                    $token = $user->createToken('ComptableAuthToken')->plainTextToken;
                    
                    $redirect = $user->user_type === 'Agent de recouvrement' 
                        ? route('accounting.agent.dashboard') 
                        : route('accounting.dashboard');
                    
                    Log::info('Connexion comptable réussie', [
                        'user_id' => $user->id,
                        'code_id' => $user->code_id,
                        'role' => $user->user_type
                    ]);

                    return response()->json([
                        'user' => $user,
                        'token' => $token,
                        'user_type' => 'comptable',
                        'role' => $user->user_type,
                        'redirect' => $redirect
                    ]);
                } else {
                    Log::warning('Mot de passe comptable incorrect', ['code_id' => $request->code_id]);
                }
            }

            // Si aucun des deux
            Log::warning('Aucun utilisateur trouvé avec ce code_id', ['code_id' => $request->code_id]);

            return response()->json([
                'error' => 'Identifiants incorrects',
                'message' => 'Le code ID ou le mot de passe est incorrect.'
            ], 401);

        } catch (Exception $e) {
            Log::error('ERREUR CONNEXION:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'code_id' => $request->code_id ?? 'null'
            ]);

            return response()->json([
                'error' => 'Erreur de connexion',
                'message' => 'Une erreur est survenue lors de la connexion.',
                'debug' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
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
            return $this->handleUserOTP($locataire, 'locataire');
        }

        $comptable = Comptable::where('code_id', $request->code_id)->first();
        if ($comptable) {
            return $this->handleUserOTP($comptable, 'comptable');
        }

        return response()->json([
            'success' => false,
            'message' => 'Aucun utilisateur trouvé avec ce code ID'
        ], 404);
    }

    protected function handleUserOTP($user, $userType)
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
     *              required={"code_id", "type", "otp", "password", "password_confirmation"},
     *              @OA\Property(property="code_id", type="string", example="LOC12345"),
     *              @OA\Property(property="type", type="string", enum={"locataire", "comptable"}, example="locataire"),
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
            'type' => 'required|in:locataire,comptable',
            'otp' => 'required|digits:6',
            'password' => 'required|min:8|confirmed'
        ]);

        // Trouver l'utilisateur
        $user = $request->type === 'locataire' 
            ? Locataire::where('code_id', $request->code_id)->first()
            : Comptable::where('code_id', $request->code_id)->first();

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
}