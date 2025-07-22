<?php

namespace App\Http\Controllers\Locataire;

use App\Http\Controllers\Controller;
use App\Models\EtatLieu;
use App\Models\EtatLieuSorti;
use App\Models\VerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EtatLieuLocataireController extends Controller
{
    public function etat_lieu() {
        // Récupérer le locataire connecté
        $locataire = Auth::user();
        
        // Récupérer le dernier code de vérification non expiré
        $verificationData = VerificationCode::where('locataire_id', $locataire->id)
            ->where('expires_at', '>', now())
            ->where('is_used', false)
            ->first();
        
        // Récupérer le comptable associé au locataire
        $comptable = $locataire->comptable;
        
        // Récupérer l'état des lieux s'il existe
        $etatLieu = EtatLieu::where('locataire_id', $locataire->id)
            ->where('bien_id', $locataire->bien_id)
            ->first();
        
        // Décoder les champs JSON si ils existent
        if ($etatLieu) {
            if ($etatLieu->parties_communes) {
                $etatLieu->parties_communes = json_decode($etatLieu->parties_communes, true);
            }
            if ($etatLieu->chambres) {
                $etatLieu->chambres = json_decode($etatLieu->chambres, true);
            }
        }

        // Récupérer l'état des lieux s'il existe
        $etatLieuSortie = EtatLieuSorti::where('locataire_id', $locataire->id)
            ->where('bien_id', $locataire->bien_id)
            ->first();
        
        // Décoder les champs JSON si ils existent
        if ($etatLieuSortie) {
            if ($etatLieuSortie->parties_communes) {
                $etatLieuSortie->parties_communes = json_decode($etatLieuSortie->parties_communes, true);
            }
            if ($etatLieuSortie->chambres) {
                $etatLieuSortie->chambres = json_decode($etatLieuSortie->chambres, true);
            }
        }
        
        // Lire le QR code depuis le stockage
        $qrCodeBase64 = null;
        if ($verificationData && Storage::exists($verificationData->path_qr_code)) {
            $qrCodeBase64 = base64_encode(Storage::get($verificationData->path_qr_code));
        }
        
        return view('locataire.etat_lieu.show', [
            'locataire' => $locataire,
            'verificationCode' => $verificationData->code ?? null,
            'qrCodeBase64' => $qrCodeBase64,
            'expiresAt' => $verificationData->expires_at ?? null,
            'comptable' => $comptable,
            'etatLieu' => $etatLieu,
            'etatLieuSortie' => $etatLieuSortie,
        ]);
    }

    public function confirmEntree($id)
    {
        try {
            $etatLieu = EtatLieu::findOrFail($id);
            
            if ($etatLieu->status_etat_entre === 'Oui') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet état des lieux est déjà confirmé'
                ], 400);
            }
            
            $etatLieu->status_etat_entre = 'Oui';
            $etatLieu->save();
            
            return response()->json([
                'success' => true,
                'message' => 'État des lieux confirmé avec succès'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
    public function confirmSortie($id)
    {
        try {
            $etatLieuSortie = EtatLieuSorti::findOrFail($id);
            
            if ($etatLieuSortie->status_sorti === 'Oui') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet état des lieux est déjà confirmé'
                ], 400);
            }
            
            $etatLieuSortie->status_sorti = 'Oui';
            $etatLieuSortie->save();
            
            return response()->json([
                'success' => true,
                'message' => 'État des lieux confirmé avec succès'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
}
