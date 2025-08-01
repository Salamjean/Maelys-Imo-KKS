<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Bien;
use App\Models\Locataire;
use App\Models\Proprietaire;
use App\Models\Visite;
use Exception;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $adminId = auth()->guard('admin')->id();
        // Totaux généraux
        $totalAppartements = Bien::whereNull('agence_id')
                ->whereNull('proprietaire_id')
                ->where('type', 'Appartement')->count();
        $totalMaisons = Bien::whereNull('agence_id')
                ->whereNull('proprietaire_id')  
                ->where('type', 'Maison')->count();
        $totalMagasins = Bien::whereNull('agence_id')
                ->whereNull('proprietaire_id')
                ->where('type', 'Magasin')->count();
        // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')
                        ->whereHas('bien', function ($query) {
                             $query->whereNull('agence_id');  // Filtrer par l'ID de l'agence
                             $query->whereNull('proprietaire_id') // 1er cas: bien sans propriétaire
                                ->orWhereHas('proprietaire', function($q) {
                                    $q->where('gestion', 'agence'); // 2ème cas: bien avec propriétaire gestion agence
                                });
                        })
                        ->count();
        // Statistiques par période
        $stats = [
            'day' => [
                'appartements' => Bien::whereNull('agence_id')
                ->whereNull('proprietaire_id')
                ->where('type', 'Appartement')->whereDate('created_at', today())->count(),
                'maisons' => Bien::whereNull('agence_id')
                ->whereNull('proprietaire_id')
                ->where('type', 'Maison')->whereDate('created_at', today())->count(),
                'magasins' => Bien::whereNull('agence_id')
                ->whereNull('proprietaire_id')
                ->where('type', 'Magasin')->whereDate('created_at', today())->count()
            ],
            'week' => [
                'appartements' => Bien::whereNull('agence_id')
                ->whereNull('proprietaire_id')
                ->where('type', 'Appartement')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'maisons' => Bien::whereNull('agence_id')
                ->whereNull('proprietaire_id')
                ->where('type', 'Maison')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'magasins' => Bien::whereNull('agence_id')
                ->whereNull('proprietaire_id')
                ->where('type', 'Magasin')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count()
            ],
            'month' => [
                'appartements' => Bien::whereNull('agence_id')
                ->whereNull('proprietaire_id')
                ->where('type', 'Appartement')->whereMonth('created_at', now()->month)->count(),
                'maisons' => Bien::whereNull('agence_id')
                ->whereNull('proprietaire_id')
                ->where('type', 'Maison')->whereMonth('created_at', now()->month)->count(),
                'magasins' => Bien::whereNull('agence_id')
                ->whereNull('proprietaire_id')
                ->where('type', 'Magasin')->whereMonth('created_at', now()->month)->count()
            ]
        ];
    
        // Biens récents
        $recentBiens = Bien::whereNull('agence_id')
                ->whereNull('proprietaire_id')
                          ->with('agence')
                          ->orderBy('created_at', 'desc')
                          ->take(5)
                          ->get();
    
        return view('admin.dashboard', [
            'totalAppartements' => $totalAppartements,
            'totalMaisons' => $totalMaisons,
            'totalMagasins' => $totalMagasins,
            'stats' => $stats,
            'recentBiens' => $recentBiens,
            'pendingVisits' => $pendingVisits
        ]);
    }

    public function logout()
    {
        auth()->guard('admin')->logout();
        return redirect()->route('admin.login');
    }

    public function register()
    {
        return view('admin.auth.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:admins',
            'password' => 'required|string|min:8|same:password_confirm',
            'password_confirm' => 'required|string|min:8|same:password',
        ],[
            'name.required' => 'Le nom est requis.',
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.same' => 'Les mots de passe ne correspondent pas.',
            'password_confirm.required' => 'La confirmation du mot de passe est requise.',
            'password_confirm.min' => 'La confirmation du mot de passe doit contenir au moins 8 caractères.',
            'password_confirm.same' => 'Les mots de passe ne correspondent pas.',
        ]);

        try {
            $admin = new Admin();
            $admin->name = $request->name;
            $admin->email = $request->email;
            $admin->password = bcrypt($request->password);
            $admin->save();
            return redirect()->route('admin.login')->with('success', 'Inscription réussie. Vous pouvez vous connecter.');
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    public function login()
    {
        return view('admin.auth.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
        ],[
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être valide.',
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        if (auth()->guard('admin')->attempt($request->only('email', 'password'))) {
            return redirect()->route('admin.dashboard')->with('success', 'Connexion réussie.');
        }

        return back()->withErrors([
            'email' => 'Les informations d\'identification fournies ne correspondent pas à nos enregistrements.',
        ]);
    }


    public function abonnement()
    {
        $owners = Proprietaire::paginate(10);
        return view('admin.abonnement.abonnement',compact('owners'));
    }

    public function move(){
             // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')
                        ->whereHas('bien', function ($query) {
                             $query->whereNull('agence_id');  // Filtrer par l'ID de l'agence
                             $query->whereNull('proprietaire_id') // 1er cas: bien sans propriétaire
                                ->orWhereHas('proprietaire', function($q) {
                                    $q->where('gestion', 'agence'); // 2ème cas: bien avec propriétaire gestion agence
                                });
                        })
                        ->count();
        // Récupération de tous les locataires
        $locataires = Locataire::where('status','Inactif')
                    ->whereNull('bien_id')
                    ->paginate(6);

        
         // Ajout d'une propriété à chaque locataire pour déterminer si le bouton doit être affiché
        $locataires->getCollection()->transform(function($locataire) {
            $today = now()->format('d');
            $currentMonthPaid = $locataire->paiements->isNotEmpty();
            $locataire->show_reminder_button = ($locataire->bien->date_fixe ?? '10' == $today) && !$currentMonthPaid;
            return $locataire;
        });
        return view('admin.locataire.move', compact('locataires', 'pendingVisits'));
    }
}
