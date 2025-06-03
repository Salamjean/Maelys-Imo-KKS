<?php

namespace App\Http\Controllers\Proprietaire;
use App\Http\Controllers\Controller;
use App\Mail\OwnerPasswordResetMail;
use App\Models\Proprietaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OwnerPasswordResetController extends Controller
{
     // Afficher le formulaire de demande
    public function showLinkRequestForm()
    {
        return view('proprietaire.auth.resetpassword.forgot-password');
    }

    // Envoyer le lien de réinitialisation
     public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        $agence = Proprietaire::where('email', $request->email)->first();
        
        if (!$agence) {
            return back()->withErrors(['email' => 'Aucun propriétaire trouvée avec cette adresse email']);
        }
        
        $token = Str::random(60);
        $agence->password_reset_token = $token;
        $agence->password_reset_expires = now()->addHours(1);
        $agence->save();
        
        // Génération du lien corrigée
        $resetLink = route('owner.reset', [
            'email' => urlencode($agence->email), 
            'token' => $token
        ]);
        
        Mail::to($agence->email)->send(new OwnerPasswordResetMail($resetLink));
        
        return back()->with('status', 'Un lien de réinitialisation a été envoyé');
    }

    // Afficher le formulaire de réinitialisation (CORRIGÉ)
    public function showResetForm(Request $request, $email, $token)
    {
        $email = urldecode($email);
        $agence = Proprietaire::where('email', $email)->first();
        
        if (!$agence || 
            !$agence->password_reset_token || 
            $agence->password_reset_token !== $token ||
            $agence->password_reset_expires < now()) {
            abort(404, 'Lien invalide ou expiré');
        }
        
        return view('proprietaire.auth.resetpassword.reset-password', [
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
        
        $agence = Proprietaire::where('email', $request->email)
                      ->where('password_reset_token', $request->token)
                      ->first();
        
        if (!$agence || $agence->password_reset_expires < now()) {
            return back()->withErrors(['email' => 'Lien invalide ou expiré']);
        }
        
        $agence->password = Hash::make($request->password);
        $agence->password_reset_token = null;
        $agence->password_reset_expires = null;
        $agence->save();
        
        return redirect()->route('owner.login')->with('success', 'Mot de passe réinitialisé avec succès');
    }
}
