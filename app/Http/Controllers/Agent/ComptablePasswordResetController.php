<?php

namespace App\Http\Controllers\Agent;
use App\Http\Controllers\Controller;
use App\Mail\ComptablePasswordResetMail;
use App\Models\Comptable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ComptablePasswordResetController extends Controller
{
     // Afficher le formulaire de demande
    public function showLinkRequestForm()
    {
        return view('comptable.auth.resetpassword.forgot-password');
    }

    // Envoyer le lien de réinitialisation
     public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        $comptable = Comptable::where('email', $request->email)->first();
        
        if (!$comptable) {
            return back()->withErrors(['email' => 'Aucun comptable trouvé avec cette adresse email']);
        }
        
        $token = Str::random(60);
        $comptable->password_reset_token = $token;
        $comptable->password_reset_expires = now()->addHours(1);
        $comptable->save();
        
        // Génération du lien corrigée
        $resetLink = route('comptable.reset', [
            'email' => urlencode($comptable->email), 
            'token' => $token
        ]);
        
        Mail::to($comptable->email)->send(new ComptablePasswordResetMail($resetLink));
        
        return redirect()->route('comptable.login')->with('success', 'Un lien de réinitialisation a été envoyé');
    }

    // Afficher le formulaire de réinitialisation (CORRIGÉ)
    public function showResetForm(Request $request, $email, $token)
    {
        $email = urldecode($email);
        $comptable = Comptable::where('email', $email)->first();
        
        if (!$comptable || 
            !$comptable->password_reset_token || 
            $comptable->password_reset_token !== $token ||
            $comptable->password_reset_expires < now()) {
            abort(404, 'Lien invalide ou expiré');
        }
        
        return view('comptable.auth.resetpassword.reset-password', [
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
        
        $comptable = Comptable::where('email', $request->email)
                      ->where('password_reset_token', $request->token)
                      ->first();
        
        if (!$comptable || $comptable->password_reset_expires < now()) {
            return back()->withErrors(['email' => 'Lien invalide ou expiré']);
        }
        
        $comptable->password = Hash::make($request->password);
        $comptable->password_reset_token = null;
        $comptable->password_reset_expires = null;
        $comptable->save();
        
        return redirect()->route('comptable.login')->with('success', 'Mot de passe réinitialisé avec succès');
    }
}
