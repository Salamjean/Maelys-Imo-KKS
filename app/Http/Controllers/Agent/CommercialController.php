<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Commercial;
use App\Models\ResetCodePasswordCommercial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CommercialController extends Controller
{
    public function dashboard()
    {
        Carbon::setLocale('fr');
        $commercial = Auth::guard('commercial')->user();

        // Récupération des statistiques réelles du commercial
        $totalAgences = \App\Models\Agence::where('commercial_id', $commercial->code_id)->count();
        $totalProprietaires = \App\Models\Proprietaire::where('commercial_id', $commercial->code_id)->count();
        $totalBiens = \App\Models\Bien::where('commercial_id', $commercial->code_id)->count();

        // Activités récentes (mélange d'agences et propriétaires)
        $recentAgences = \App\Models\Agence::where('commercial_id', $commercial->code_id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($item) {
                $item->type_label = 'Agence';
                $item->color = 'primary';
                return $item;
            });

        $recentProprietaires = \App\Models\Proprietaire::where('commercial_id', $commercial->code_id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($item) {
                $item->type_label = 'Propriétaire';
                $item->color = 'info';
                return $item;
            });

        $recentActivities = $recentAgences->merge($recentProprietaires)->sortByDesc('created_at')->take(5);

        return view('commercial.commercial_dashboard', compact(
            'commercial', 
            'totalAgences', 
            'totalProprietaires', 
            'totalBiens', 
            'recentActivities'
        ));
    }

    public function statistics()
    {
        Carbon::setLocale('fr');
        $commercial = Auth::guard('commercial')->user();
        $today = Carbon::today();

        // Statistiques du jour
        $dailyAgences = \App\Models\Agence::where('commercial_id', $commercial->code_id)->whereDate('created_at', $today)->count();
        $dailyProprietaires = \App\Models\Proprietaire::where('commercial_id', $commercial->code_id)->whereDate('created_at', $today)->count();
        $dailyBiens = \App\Models\Bien::where('commercial_id', $commercial->code_id)->whereDate('created_at', $today)->count();

        // Statistiques globales
        $totalAgences = \App\Models\Agence::where('commercial_id', $commercial->code_id)->count();
        $totalProprietaires = \App\Models\Proprietaire::where('commercial_id', $commercial->code_id)->count();
        $totalBiens = \App\Models\Bien::where('commercial_id', $commercial->code_id)->count();

        // Historique journalier (7 derniers jours)
        $history = [];
        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::today()->subDays($i);
            $history[] = [
                'date' => $date->translatedFormat('d F Y'),
                'agences' => \App\Models\Agence::where('commercial_id', $commercial->code_id)->whereDate('created_at', $date)->count(),
                'proprietaires' => \App\Models\Proprietaire::where('commercial_id', $commercial->code_id)->whereDate('created_at', $date)->count(),
                'biens' => \App\Models\Bien::where('commercial_id', $commercial->code_id)->whereDate('created_at', $date)->count(),
            ];
        }

        // Statistiques hebdomadaires (pour le graphique)
        $stats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $stats['labels'][] = $date->translatedFormat('D d M');
            $stats['agences'][] = \App\Models\Agence::where('commercial_id', $commercial->code_id)->whereDate('created_at', $date)->count();
            $stats['proprietaires'][] = \App\Models\Proprietaire::where('commercial_id', $commercial->code_id)->whereDate('created_at', $date)->count();
            $stats['biens'][] = \App\Models\Bien::where('commercial_id', $commercial->code_id)->whereDate('created_at', $date)->count();
        }

        return view('commercial.statistics', compact(
            'commercial',
            'dailyAgences',
            'dailyProprietaires',
            'dailyBiens',
            'totalAgences',
            'totalProprietaires',
            'totalBiens',
            'history',
            'stats'
        ));
    }

    public function exportStatisticsPDF()
    {
        Carbon::setLocale('fr');
        $commercial = Auth::guard('commercial')->user();
        
        // Données globales
        $totalAgences = \App\Models\Agence::where('commercial_id', $commercial->code_id)->count();
        $totalProprietaires = \App\Models\Proprietaire::where('commercial_id', $commercial->code_id)->count();
        $totalBiens = \App\Models\Bien::where('commercial_id', $commercial->code_id)->count();

        // Historique 30 jours
        $history = [];
        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::today()->subDays($i);
            $agences = \App\Models\Agence::where('commercial_id', $commercial->code_id)->whereDate('created_at', $date)->count();
            $proprietaires = \App\Models\Proprietaire::where('commercial_id', $commercial->code_id)->whereDate('created_at', $date)->count();
            $biens = \App\Models\Bien::where('commercial_id', $commercial->code_id)->whereDate('created_at', $date)->count();

            if ($agences > 0 || $proprietaires > 0 || $biens > 0) {
                $history[] = [
                    'date' => $date->translatedFormat('d/m/Y'),
                    'agences' => $agences,
                    'proprietaires' => $proprietaires,
                    'biens' => $biens,
                ];
            }
        }

        $pdf = \PDF::loadView('commercial.statistics_pdf', compact(
            'commercial',
            'totalAgences',
            'totalProprietaires',
            'totalBiens',
            'history'
        ));

        return $pdf->download('rapport-activite-' . $commercial->name . '-' . now()->format('d-m-Y') . '.pdf');
    }

    public function defineAccess($email)
    {
        $checkCommercialExiste = Commercial::where('email', $email)->first();
        if ($checkCommercialExiste) {
            return view('commercial.auth.validate', compact('email'));
        } else {
            return redirect()->route('login')->with('error', 'Email inconnu');
        }
    }

    public function submitDefineAccess(Request $request)
    {
        $request->validate([
            'code' => 'required|exists:reset_code_password_commercials,code',
            'password' => 'required|same:password_confirm',
            'password_confirm' => 'required|same:password',
        ], [
            'code.exists' => 'Le code de réinitialisation est invalide.',
            'code.required' => 'Le code de réinitialisation est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.same' => 'Les mots de passe doivent être identiques.',
            'password_confirm.same' => 'Les mots de passe doivent être identiques.',
            'password_confirm.required' => 'La confirmation du mot de passe est obligatoire.',
        ]);

        try {
            $commercial = Commercial::where('email', $request->email)->first();

            if ($commercial) {
                $commercial->password = Hash::make($request->password);
                $commercial->save();

                ResetCodePasswordCommercial::where('email', $commercial->email)->delete();

                return redirect()->route('login')->with('success', 'Compte mis à jour avec succès. Vous pouvez maintenant vous connecter.');
            } else {
                return redirect()->route('login')->with('error', 'Email inconnu');
            }
        } catch (\Exception $e) {
            Log::error('Error updating commercial profile: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue : ' . $e->getMessage())->withInput();
        }
    }

    public function logout(Request $request)
    {
        Auth::guard('commercial')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Déconnexion réussie.');
    }
}
