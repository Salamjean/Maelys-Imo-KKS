<?php

namespace App\Http\Controllers\Api\Locataire;

use App\Http\Controllers\Controller;
use App\Mail\ContactAgencyMail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Locataire",
 *     description="Points de terminaison pour la gestion du profil du locataire"
 * )
 */
class ContacterAgenceController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/tenant/contact/agency",
     *      operationId="sendEmailToAgency",
     *      tags={"Locataire"},
     *      summary="Envoyer un message à l'agence",
     *      description="Permet au locataire authentifié d'envoyer un email à son agence.",
     *      security={{"sanctum": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Données du message à envoyer",
     *          @OA\JsonContent(
     *              required={"subject", "message", "agency_email"},
     *              @OA\Property(property="subject", type="string", example="Problème avec la plomberie"),
     *              @OA\Property(property="message", type="string", example="Bonjour, je vous contacte car j'ai une fuite d'eau dans la salle de bain."),
     *              @OA\Property(property="agency_email", type="string", format="email", example="agence.immobiliere@example.com")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Message envoyé avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Message envoyé avec succès")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Non authentifié",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Authentification requise")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Erreur de validation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Erreur de validation"),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Erreur serveur",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Erreur serveur: ...")
     *          )
     *      )
     * )
     */
    public function sendEmailToAgency(Request $request)
    {
        if (!auth('sanctum')->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise'
            ], 401);
        }

        // Debug: Afficher le contenu brut de la requête
        Log::info('Raw request content:', ['content' => $request->getContent()]);

        try {
            // Validation manuelle si nécessaire
            $data = json_decode($request->getContent(), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $data = $request->all();
            }

            $validator = Validator::make($data, [
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
                'agency_email' => 'required|email'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = auth('sanctum')->user();

            Mail::to($data['agency_email'])->send(new ContactAgencyMail(
                $data['subject'],
                $data['message'],
                $user->name,
                $user->email,
                $user->bien
            ));

            return response()->json([
                'success' => true,
                'message' => 'Message envoyé avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Full error:', ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: '.$e->getMessage()
            ], 500);
        }
    }
}