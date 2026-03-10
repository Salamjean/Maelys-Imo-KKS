<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Comptable;
use App\Models\Locataire;
use App\Models\Proprietaire;
use App\Mail\PasswordResetMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class UnifiedForgotPasswordController extends Controller
{
    /**
     * Affiche le formulaire de demande de réinitialisation.
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Envoie le lien de réinitialisation par email après avoir trouvé l'utilisateur par Code ID.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'code_id' => 'required',
        ], [
            'code_id.required' => 'Le Code ID est obligatoire.',
        ]);

        $codeId = strtoupper($request->code_id);
        $user = null;
        $guard = null;

        // Chercher l'utilisateur dans les différents modèles
        if ($u = Locataire::where('code_id', $codeId)->first()) {
            $user = $u;
            $guard = 'locataire';
        } elseif ($u = Comptable::where('code_id', $codeId)->first()) {
            $user = $u;
            $guard = 'comptable';
        } elseif ($u = Agence::where('code_id', $codeId)->first()) {
            $user = $u;
            $guard = 'agence';
        } elseif ($u = Proprietaire::where('code_id', $codeId)->first()) {
            $user = $u;
            $guard = 'owner';
        }

        if (!$user) {
            return back()->withErrors(['code_id' => 'Aucun utilisateur trouvé avec ce Code ID.']);
        }

        if (!$user->email) {
            return back()->withErrors(['code_id' => 'Cet utilisateur n\'a pas d\'adresse email enregistrée.']);
        }

        try {
            $token = Str::random(60);
            $user->password_reset_token = $token;
            $user->password_reset_expires = now()->addHours(2);
            $user->save();

            $resetLink = route('unified.password.reset', [
                'code_id' => $user->code_id,
                'token' => $token
            ]);

            Mail::to($user->email)->send(new PasswordResetMail($resetLink));

            return redirect()->route('login')->with('success', 'Un lien de réinitialisation a été envoyé à votre adresse email.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de l\'email de réinitialisation : ' . $e->getMessage());
            return back()->withErrors(['error' => 'Une erreur est survenue lors de l\'envoi de l\'email. Veuillez réessayer plus tard.']);
        }
    }

    /**
     * Affiche le formulaire de réinitialisation du mot de passe.
     */
    public function showResetForm($code_id, $token)
    {
        return view('auth.passwords.reset', [
            'code_id' => $code_id,
            'token' => $token
        ]);
    }

    /**
     * Traite la réinitialisation du mot de passe.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'code_id' => 'required',
            'token' => 'required',
            'password' => 'required|min:8|confirmed',
        ], [
            'password.required' => 'Le nouveau mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $codeId = strtoupper($request->code_id);
        $user = null;

        // Chercher l'utilisateur
        if ($u = Locataire::where('code_id', $codeId)->first()) {
            $user = $u;
        } elseif ($u = Comptable::where('code_id', $codeId)->first()) {
            $user = $u;
        } elseif ($u = Agence::where('code_id', $codeId)->first()) {
            $user = $u;
        } elseif ($u = Proprietaire::where('code_id', $codeId)->first()) {
            $user = $u;
        }

        if (
            !$user ||
            !$user->password_reset_token ||
            $user->password_reset_token !== $request->token ||
            $user->password_reset_expires < now()
        ) {
            return redirect()->route('unified.password.request')->withErrors(['error' => 'Le lien de réinitialisation est invalide ou a expiré.']);
        }

        try {
            $user->password = Hash::make($request->password);
            $user->password_reset_token = null;
            $user->password_reset_expires = null;
            $user->save();

            return redirect()->route('login')->with('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la réinitialisation du mot de passe : ' . $e->getMessage());
            return back()->withErrors(['error' => 'Une erreur est survenue. Veuillez réessayer plus tard.']);
        }
    }
}
