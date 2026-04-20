<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Abonnement;

class UnifiedLoginController extends Controller
{
    /**
     * Affiche le formulaire de connexion unifié
     */
    public function showLoginForm()
    {
        // Si l'utilisateur est déjà connecté à l'un des espaces, on le redirige vers son tableau de bord approprié
        if (Auth::guard('agence')->check())
            return redirect()->route('agence.dashboard');
        if (Auth::guard('owner')->check())
            return redirect()->route('owner.dashboard');
        if (Auth::guard('locataire')->check())
            return redirect()->route('locataire.dashboard');
        if (Auth::guard('commercial')->check())
            return redirect()->route('commercial.dashboard');
        if (Auth::guard('comptable')->check()) {
            $user = Auth::guard('comptable')->user();
            if ($user->user_type === 'Agent de recouvrement') {
                return redirect()->route('accounting.agent.dashboard');
            }
            return redirect()->route('accounting.dashboard');
        }

        return view('auth.login');
    }

    /**
     * Gère la tentative d'authentification pour tous les types d'utilisateur (sauf Admin)
     */
    public function authenticate(Request $request)
    {
        // 1. Validation 
        $request->validate([
            'identifiant' => 'required',
            'password' => 'required|min:8',
        ], [
            'identifiant.required' => 'Le Code ID est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        $loginInput = $request->input('identifiant');
        $password = $request->input('password');

        // 2. Formatage du Code ID
        $credentials = [
            'code_id' => strtoupper($loginInput),
            'password' => $password
        ];

        try {
            // 3. Essayer chaque Gard (Guard) itérativement

            // --- A. LOCATAIRE ---
            if (Auth::guard('locataire')->attempt($credentials)) {
                $locataireUser = Auth::guard('locataire')->user();
                if (!$locataireUser->bien) {
                    Auth::guard('locataire')->logout();
                    return redirect()->back()->withInput($request->except('password'))
                        ->with('error', 'Vous ne louez plus de bien. Votre accès a été désactivé. Veuillez contacter votre agence/propriétaire.');
                }
                return redirect()->route('locataire.dashboard')->with('success', 'Bienvenue sur votre espace.');
            }

            // --- B. COMMERCIAL ---
            if (Auth::guard('commercial')->attempt($credentials)) {
                $commercial = Auth::guard('commercial')->user();
                if (!$commercial->is_active) {
                    Auth::guard('commercial')->logout();
                    return redirect()->back()
                        ->with('error', 'Votre compte est désactivé. Veuillez contacter l\'administrateur.')
                        ->withInput($request->except('password'));
                }
                return redirect()->route('commercial.dashboard')->with('success', 'Bienvenue sur votre espace Commercial.');
            }

            // --- C. COMPTABLE / AGENT ---
            if (Auth::guard('comptable')->attempt($credentials)) {
                $user = Auth::guard('comptable')->user();
                if ($user->user_type === 'Agent de recouvrement') {
                    return redirect()->route('accounting.agent.dashboard')->with('success', 'Bienvenue sur votre page Agent.');
                } elseif ($user->user_type === 'Comptable') {
                    return redirect()->route('accounting.dashboard')->with('success', 'Bienvenue sur votre page Comptable.');
                }
                return redirect()->route('login')->with('error', 'Type d\'utilisateur inconnu.');
            }

            // --- D. AGENCE ---
            if (Auth::guard('agence')->attempt($credentials)) {
                $agence = Auth::guard('agence')->user();
                $abonnement = Abonnement::where('agence_id', $agence->code_id)
                    ->latest('date_fin')
                    ->first();

                if ($abonnement && $abonnement->statut === 'actif' && $abonnement->date_fin >= now()) {
                    return redirect()->route('agence.dashboard')->with('success', 'Bienvenue sur votre tableau de bord.');
                }

                // Redirection page abonnement
                return redirect()->route('page.abonnement.agence')->with('error', $this->getAbonnementMessage($abonnement));
            }

            // --- D. PROPRIÉTAIRE ---
            if (Auth::guard('owner')->attempt($credentials)) {
                $owner = Auth::guard('owner')->user();
                $abonnement = Abonnement::where('proprietaire_id', $owner->code_id)
                    ->latest('date_fin')
                    ->first();

                if ($abonnement && $abonnement->statut === 'actif' && $abonnement->date_fin >= now()) {
                    return redirect()->route('owner.dashboard')->with('success', 'Bienvenue sur votre tableau de bord.');
                }

                return redirect()->route('page.abonnement')->with('error', $this->getAbonnementMessage($abonnement));
            }

            // Si aucune authentification n'a réussi
            return redirect()->back()
                ->with('error', 'Identifiant ou mot de passe incorrect.')
                ->withInput($request->except('password'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de la connexion unifiée : ' . $e->getMessage());
            return redirect()->back()->with('error', 'Une erreur technique est survenue.');
        }
    }

    /**
     * Détermine le message d'erreur d'abonnement
     */
    private function getAbonnementMessage($abonnement): string
    {
        if (!$abonnement) {
            return 'Aucun abonnement actif trouvé';
        }

        return match ($abonnement->statut) {
            'en_attente' => 'Votre paiement est en cours de validation',
            'suspendu' => 'Votre compte est suspendu',
            'actif' => $abonnement->date_fin < now()
                ? 'Votre abonnement a expiré'
                : 'Abonnement requis',
            default => 'Statut d\'abonnement non reconnu',
        };
    }
}
