<?php

namespace App\Http\Controllers\Locataire;
use App\Http\Controllers\Controller;
use App\Mail\LocatairePasswordResetMail;
use App\Models\Locataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LocatairePasswordResetController extends Controller
{
    // Afficher le formulaire de demande
    public function showLinkRequestForm()
    {
        return view('locataire.auth.resetpassword.forgot-password');
    }

    // Envoyer le lien de réinitialisation
     public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        $locataire = Locataire::where('email', $request->email)->first();
        
        if (!$locataire) {
            return back()->withErrors(['email' => 'Aucun locataire trouvée avec cette adresse email']);
        }
        
        $token = Str::random(60);
        $locataire->password_reset_token = $token;
        $locataire->password_reset_expires = now()->addHours(1);
        $locataire->save();
        
        // Génération du lien corrigée
        $resetLink = route('locataire.reset', [
            'email' => urlencode($locataire->email), 
            'token' => $token
        ]);
        
        Mail::to($locataire->email)->send(new LocatairePasswordResetMail($resetLink));
        
        return redirect()->route('locataire.login')->with('success', 'Un lien de réinitialisation a été envoyé');
    }

    // Afficher le formulaire de réinitialisation (CORRIGÉ)
    public function showResetForm(Request $request, $email, $token)
    {
        $email = urldecode($email);
        $locataire = Locataire::where('email', $email)->first();
        
        if (!$locataire || 
            !$locataire->password_reset_token || 
            $locataire->password_reset_token !== $token ||
            $locataire->password_reset_expires < now()) {
            abort(404, 'Lien invalide ou expiré');
        }
        
        return view('locataire.auth.resetpassword.reset-password', [
            'email' => $email,
            'token' => $token
        ]);
    }

    // Traiter la réinitialisation (CORRIGÉ)
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
            'token' => 'required'
        ]);
        
        $locataire = Locataire::where('email', $request->email)
                      ->where('password_reset_token', $request->token)
                      ->first();
        
        if (!$locataire || $locataire->password_reset_expires < now()) {
            return back()->withErrors(['email' => 'Lien invalide ou expiré']);
        }
        
        $locataire->password = Hash::make($request->password);
        $locataire->password_reset_token = null;
        $locataire->password_reset_expires = null;
        $locataire->save();
        
        return redirect()->route('locataire.login')->with('success', 'Mot de passe réinitialisé avec succès');
    }
}
