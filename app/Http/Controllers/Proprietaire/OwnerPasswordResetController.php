<?php

namespace App\Http\Controllers\Proprietaire;
use App\Http\Controllers\Controller;
use App\Mail\OwnerPasswordResetMail;
use App\Models\Proprietaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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
        
        return redirect()->route('owner.login')->with('success', 'Un lien de réinitialisation a été envoyé');
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


    //Gestion du profil proprietaire
    public function editProfile(){
        $proprietaire = Auth::guard('owner')->user();
        return view('proprietaire.auth.profile', compact('proprietaire'));
    }

   public function updateProfile(Request $request)
    {
        $proprietaire = Auth::guard('owner')->user();

        $rules = [
            'name' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:proprietaires,email,'.$proprietaire->id,
            'contact' => 'required|string|min:10',
            'commune' => 'required|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|same:password_confirm';
            $rules['password_confirm'] = 'string|min:8|same:password';
        }

        $request->validate($rules, [
            'password.same' => 'Les mots de passe ne correspondent pas',
            'password_confirm.same' => 'Les mots de passe ne correspondent pas',
        ]);

        try {
            // Mise à jour de l'image de profil
            if ($request->hasFile('profile_image')) {
                if ($proprietaire->profil_image) { // Notez le changement ici
                    Storage::disk('public')->delete($proprietaire->profil_image);
                }
                $proprietaire->profil_image = $request->file('profile_image')->store('profile_images', 'public'); // Et ici
            }

            // Mise à jour des informations de base
            $proprietaire->name = $request->name;
            $proprietaire->prenom = $request->prenom; // Ajout du prénom
            $proprietaire->email = $request->email;
            $proprietaire->contact = $request->contact;
            $proprietaire->commune = $request->commune;
            // Suppression de l'adresse qui n'existe pas dans la table

            if ($request->filled('password')) {
                $proprietaire->password = Hash::make($request->password);
            }

            $proprietaire->save();

            return redirect()->route('owner.dashboard')->with('success', 'Vos informations ont bien été mises à jour!');

        } catch (\Exception $e) {
            Log::error('Erreur mise à jour profil proprietaire: '.$e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la mise à jour');
        }
    }
}
