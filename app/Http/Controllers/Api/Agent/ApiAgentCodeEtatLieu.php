<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use App\Mail\VerificationCodeMail;
use App\Models\Locataire;
use App\Models\VerificationCode;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Agent - Codes État des Lieux",
 *     description="Endpoints pour la gestion des codes de vérification d'état des lieux par les agents"
 * )
 */
class ApiAgentCodeEtatLieu extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/agent/code-etat-lieux/generate",
     *     operationId="generateVerificationCode",
     *     tags={"Agent - Codes État des Lieux"},
     *     summary="Générer un nouveau code de vérification",
     *     description="Génère un code aléatoire et un QR code associé pour l'état des lieux d'un locataire",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"locataire_id"},
     *             @OA\Property(property="locataire_id", type="integer", example=1, description="ID du locataire")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Code généré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="code", type="string", example="ABC123"),
     *             @OA\Property(property="qr_code_base64", type="string", description="QR code encodé en base64"),
     *             @OA\Property(property="expires_at", type="string", format="date-time", example="2024-01-15 14:30:00"),
     *             @OA\Property(property="email_sent", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Code généré avec succès et envoyé par email")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Le champ locataire_id est requis."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\AdditionalProperties(
     *                     type="array",
     *                     @OA\Items(type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function generateCode(Request $request): JsonResponse
    {
        $request->validate([
            'locataire_id' => 'required|exists:locataires,id'
        ]);

        // Récupérer les infos du locataire
        $locataire = Locataire::find($request->locataire_id);

        // Vérifier si un code valide existe déjà
        $existingCode = VerificationCode::where('locataire_id', $request->locataire_id)
            ->where('expires_at', '>', now())
            ->where('is_used', false)
            ->first();

        if ($existingCode) {
            return response()->json([
                'success' => true,
                'code' => $existingCode->code,
                'qr_code_base64' => base64_encode(Storage::get($existingCode->path_qr_code)),
                'expires_at' => $existingCode->expires_at->toDateTimeString(),
                'message' => 'Code existant récupéré avec succès'
            ]);
        }

        // Générer un nouveau code
        $code = Str::upper(Str::random(6));
        $expiresAt = now()->addHours(2);

        // Options du QR Code
        $options = new QROptions([
            'version' => 10,
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_L,
            'scale' => 5,
            'imageBase64' => false,
            'quietzoneSize' => 2,
        ]);

        // Générer le QR code
        $qrData = $code;
        $qrcode = (new QRCode($options))->render($qrData);

        // Sauvegarder le QR code
        $qrCodePath = 'qrcodes/etat_lieux/' . $code . '.png';
        Storage::disk('public')->put($qrCodePath, $qrcode);

        // Créer l'enregistrement en base de données
        $verificationCode = VerificationCode::create([
            'locataire_id' => $request->locataire_id,
            'code' => $code,
            'path_qr_code' => $qrCodePath,
            'expires_at' => $expiresAt,
            'is_used' => false
        ]);

        // Envoyer le code par email
        try {
            Mail::to($locataire->email)->send(new VerificationCodeMail($code, $expiresAt));
            $email_sent = true;
        } catch (\Exception $e) {
            $email_sent = false;
        }

        return response()->json([
            'success' => true,
            'code' => $code,
            'qr_code_base64' => base64_encode($qrcode),
            'expires_at' => $expiresAt->toDateTimeString(),
            'email_sent' => $email_sent,
            'message' => 'Code généré avec succès' . ($email_sent ? ' et envoyé par email' : ' (mais échec envoi email)')
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/agent/code-etat-lieux/verify",
     *     operationId="verifyCode",
     *     tags={"Agent - Codes État des Lieux"},
     *     summary="Vérifier un code de vérification",
     *     description="Vérifie si un code de vérification est valide pour un locataire",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"locataire_id", "verification_code"},
     *             @OA\Property(property="locataire_id", type="integer", example=1, description="ID du locataire"),
     *             @OA\Property(property="verification_code", type="string", example="ABC123", description="Code de vérification à valider")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code vérifié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Code vérifié avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Code invalide, expiré ou déjà utilisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Code invalide, expiré ou déjà utilisé")
     *         )
     *     )
     * )
     */
    public function verifyCode(Request $request): JsonResponse
    {
        $request->validate([
            'locataire_id' => 'required|exists:locataires,id',
            'verification_code' => 'required|string|size:6'
        ]);

        // Vérifier le code dans la base de données
        $code = VerificationCode::where('locataire_id', $request->locataire_id)
            ->where('code', $request->verification_code)
            ->where('expires_at', '>', now())
            ->where('is_used', false)
            ->first();

        if (!$code) {
            return response()->json([
                'success' => false,
                'message' => 'Code invalide, expiré ou déjà utilisé'
            ], 422);
        }

        // Marquer le code comme utilisé
        $code->update(['is_used' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Code vérifié avec succès'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/agent/code-etat-lieux/qr-code",
     *     operationId="getQrCode",
     *     tags={"Agent - Codes État des Lieux"},
     *     summary="Récupérer le QR code d'un locataire",
     *     description="Récupère le QR code valide pour un locataire",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="locataire_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID du locataire"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="QR code récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="qr_code", type="string", example="http://localhost/storage/qrcodes/etat_lieux/ABC123.png"),
     *             @OA\Property(property="code", type="string", example="ABC123"),
     *             @OA\Property(property="expires_at", type="string", format="date-time", example="2024-01-15 14:30:00"),
     *             @OA\Property(property="message", type="string", example="Code QR récupéré avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Aucun code valide trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Aucun code QR valide trouvé pour ce locataire")
     *         )
     *     )
     * )
     */
    public function getQrCode(Request $request): JsonResponse
    {
        $request->validate([
            'locataire_id' => 'required|exists:verification_codes,locataire_id'
        ]);

        // Récupérer le code existant
        $existingCode = VerificationCode::where('locataire_id', $request->locataire_id)
            ->where('expires_at', '>', now())
            ->where('is_used', false)
            ->first();

        if (!$existingCode) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun code QR valide trouvé pour ce locataire'
            ], 404);
        }

        // Vérifier si le fichier QR code existe
        if (!Storage::exists($existingCode->path_qr_code)) {
            return response()->json([
                'success' => false,
                'message' => 'Le fichier QR code n\'existe pas'
            ], 404);
        }

        // Retourner le code QR en base64
        return response()->json([
            'success' => true,
            'qr_code' => Storage::url($existingCode->path_qr_code),
            'code' => $existingCode->code,
            'expires_at' => $existingCode->expires_at->toDateTimeString(),
            'message' => 'Code QR récupéré avec succès'
        ]);
    }
}