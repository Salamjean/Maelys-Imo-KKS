<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use App\Mail\VerificationCodeMail;
use App\Models\Locataire;
use App\Models\VerificationCode;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VerificationcodeApiController extends Controller
{
    public function generateCode(Request $request)
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
            // Si le fichier existe physiquement, on le retourne, sinon on devrait peut-être le régénérer (ici on suppose qu'il existe)
            $qrContent = Storage::disk('public')->exists($existingCode->path_qr_code) 
                ? base64_encode(Storage::disk('public')->get($existingCode->path_qr_code))
                : null;

            return response()->json([
                'success' => true,
                'code' => $existingCode->code,
                'qr_code_base64' => $qrContent
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
        VerificationCode::create([
            'locataire_id' => $request->locataire_id,
            'code' => $code,
            'path_qr_code' => $qrCodePath,
            'expires_at' => $expiresAt,
            'is_used' => false
        ]);

        // Envoyer le code par email (Assurez-vous que la classe Mail existe)
        try {
            Mail::to($locataire->email)->send(new VerificationCodeMail($code, $expiresAt));
        } catch (\Exception $e) {
            // On continue même si l'email échoue pour le test API, mais on pourrait loguer l'erreur
        }

        return response()->json([
            'success' => true,
            'code' => $code,
            'qr_code_base64' => base64_encode($qrcode)
        ]);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'locataire_id' => 'required|exists:locataires,id',
            'verification_code' => 'required|string',
            'generated_code' => 'required|string'
        ]);

        // Vérifier que les codes correspondent (input frontend vs input frontend/scan)
        if ($request->verification_code !== $request->generated_code) {
            return response()->json([
                'success' => false,
                'message' => 'Le code saisi ne correspond pas au code généré'
            ]);
        }

        // Vérifier le code dans la base de données (Sécurité réelle)
        $code = VerificationCode::where('locataire_id', $request->locataire_id)
            ->where('code', $request->verification_code)
            ->where('expires_at', '>', now())
            ->where('is_used', false)
            ->first();

        if (!$code) {
            return response()->json([
                'success' => false,
                'message' => 'Code invalide ou expiré'
            ]);
        }

        // Marquer le code comme utilisé
        $code->update(['is_used' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Code vérifié avec succès'
        ]);
    }
}